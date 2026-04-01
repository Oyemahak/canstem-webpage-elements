if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Canstem_Inquiry_Form_Handler' ) ) {

class Canstem_Inquiry_Form_Handler {

    const SMTP_USER        = 'canstem.frontdesk@canstemeducation.com';
    const SMTP_APP_PASS    = 'persoqionuoycbkl';
    const FROM_ALIAS_EMAIL = 'student-request@canstemeducation.com';
    const FROM_ALIAS_NAME  = 'Student Inquiry';

    const MAIL_TO          = 'canstem.frontdesk@canstemeducation.com';
    const MAIL_CC          = '';
    const MAIL_BCC         = '';

    const GOOGLE_WEBHOOK_URL = 'https://script.google.com/macros/s/AKfycbwEUL-5JX8EbmBqpdWZ35MpmmHgTnIh2CLVmpDS1_PZ2lgjhL_S28rIfk-_7fr1h7052g/exec';

    public static function boot() {
        add_action( 'phpmailer_init', [ __CLASS__, 'smtp_setup' ], 999 );

        add_action( 'wp_mail_failed', function( $err ) {
            if ( is_wp_error( $err ) ) {
                error_log( 'wp_mail_failed: ' . $err->get_error_message() );
            }
        });

        add_action( 'wp_ajax_canstem_inquiry_request', [ __CLASS__, 'handle_ajax' ] );
        add_action( 'wp_ajax_nopriv_canstem_inquiry_request', [ __CLASS__, 'handle_ajax' ] );
    }

    public static function smtp_setup( $phpmailer ) {
        if ( ! empty( $phpmailer->Mailer ) && $phpmailer->Mailer === 'smtp' ) {
            return;
        }

        $phpmailer->isSMTP();
        $phpmailer->Host       = 'smtp.gmail.com';
        $phpmailer->SMTPAuth   = true;
        $phpmailer->Port       = 587;
        $phpmailer->SMTPSecure = 'tls';

        $phpmailer->Username   = self::SMTP_USER;
        $phpmailer->Password   = self::SMTP_APP_PASS;

        $phpmailer->setFrom( self::FROM_ALIAS_EMAIL, self::FROM_ALIAS_NAME, false );
        $phpmailer->SMTPDebug   = 0;
        $phpmailer->Debugoutput = function( $str, $level ) {
            error_log( "SMTP[$level] $str" );
        };
    }

    public static function handle_ajax() {
        try {
            if ( strtoupper( $_SERVER['REQUEST_METHOD'] ?? '' ) !== 'POST' ) {
                wp_send_json_error( [ 'error' => 'Invalid method' ], 405 );
            }

            $sx = function( $v ) {
                return sanitize_text_field( wp_unslash( $v ?? '' ) );
            };

            $tx = function( $v ) {
                return sanitize_textarea_field( wp_unslash( $v ?? '' ) );
            };

            $firstName           = $sx( $_POST['firstName'] ?? '' );
            $lastName            = $sx( $_POST['lastName'] ?? '' );
            $gender              = $sx( $_POST['gender'] ?? '' );
            $studentPhone        = $sx( $_POST['studentPhone'] ?? '' );
            $studentEmail        = sanitize_email( $_POST['studentEmail'] ?? '' );
            $programInterest     = $sx( $_POST['programInterest'] ?? '' );
            $programOtherDetails = $tx( $_POST['programOtherDetails'] ?? '' );
            $hearAbout           = $sx( $_POST['hearAbout'] ?? '' );
            $hearOtherSpecify    = $sx( $_POST['hearOtherSpecify'] ?? '' );
            $otherRequirements   = $tx( $_POST['otherRequirements'] ?? '' );

            if (
                ! $firstName ||
                ! $lastName ||
                ! $gender ||
                ! $studentPhone ||
                ! $studentEmail ||
                ! $programInterest ||
                ! $hearAbout
            ) {
                wp_send_json_error( [ 'error' => 'Please fill all required fields.' ], 400 );
            }

            if ( $programInterest === 'Other' && ! $programOtherDetails ) {
                wp_send_json_error( [ 'error' => 'Please fill "Tell us more about your requirements".' ], 400 );
            }

            if ( ! is_email( $studentEmail ) ) {
                wp_send_json_error( [ 'error' => 'Please enter a valid email address.' ], 400 );
            }

            $studentName = trim( $firstName . ' ' . $lastName );
            $submittedAt = current_time( 'mysql' );

            $allowed_exts = [ 'doc', 'docx', 'pdf', 'txt', 'rtf', 'xls', 'xlsx', 'bmp', 'gif', 'jpg', 'jpeg', 'png' ];

            $email_attachments = [];
            $uploaded_doc_names = [];

            $uploads = wp_upload_dir();
            $tmpdir  = trailingslashit( $uploads['basedir'] ) . 'canstem-inquiry-temp';
            if ( ! file_exists( $tmpdir ) ) {
                wp_mkdir_p( $tmpdir );
            }

            $file_map = [
                'transcriptFile'    => 'Transcript 1 - Report card',
                'additionalDocFile' => 'Additional Document 1',
                'pictureIdFile'     => 'Picture ID Passport',
            ];

            foreach ( $file_map as $field => $label ) {
                if ( empty( $_FILES[ $field ]['name'] ) ) {
                    continue;
                }

                if ( ! empty( $_FILES[ $field ]['error'] ) && $_FILES[ $field ]['error'] !== UPLOAD_ERR_OK ) {
                    wp_send_json_error( [ 'error' => $label . ' upload failed.' ], 400 );
                }

                if ( ! is_uploaded_file( $_FILES[ $field ]['tmp_name'] ) ) {
                    wp_send_json_error( [ 'error' => 'Invalid uploaded file for ' . $label ], 400 );
                }

                $name = sanitize_file_name( $_FILES[ $field ]['name'] );
                $ext  = strtolower( pathinfo( $name, PATHINFO_EXTENSION ) );

                if ( ! in_array( $ext, $allowed_exts, true ) ) {
                    wp_send_json_error( [ 'error' => 'Invalid file type uploaded for ' . $label ], 400 );
                }

                $bin = file_get_contents( $_FILES[ $field ]['tmp_name'] );
                if ( $bin === false ) {
                    wp_send_json_error( [ 'error' => 'Could not read uploaded file for ' . $label ], 400 );
                }

                $uploaded_doc_names[] = $label;

                $tmpfile = trailingslashit( $tmpdir ) . wp_unique_filename( $tmpdir, $name );
                if ( file_put_contents( $tmpfile, $bin ) !== false ) {
                    $email_attachments[] = $tmpfile;
                }
            }

            $google_payload = [
                'type'                => 'Inquiry',
                'submittedAt'         => $submittedAt,
                'firstName'           => $firstName,
                'lastName'            => $lastName,
                'fullName'            => $studentName,
                'gender'              => $gender,
                'studentPhone'        => $studentPhone,
                'studentEmail'        => $studentEmail,
                'programInterest'     => $programInterest,
                'programOtherDetails' => $programOtherDetails,
                'hearAbout'           => $hearAbout,
                'hearOtherSpecify'    => $hearOtherSpecify,
                'otherRequirements'   => $otherRequirements,
                'uploadedDocuments'   => $uploaded_doc_names,
                'attachments'         => []
            ];

            $google_result = self::post_json( self::GOOGLE_WEBHOOK_URL, $google_payload );

            if ( empty( $google_result['ok'] ) ) {
                foreach ( $email_attachments as $tmp ) {
                    @unlink( $tmp );
                }
                $msg = ! empty( $google_result['error'] ) ? $google_result['error'] : 'Google sync failed.';
                wp_send_json_error( [ 'error' => $msg ], 500 );
            }

            $subject = sprintf(
                'Inquiry Submission — %s — %s',
                $studentName,
                $programInterest
            );

            $rows   = [];
            $rows[] = self::tr( 'Submitted At', esc_html( $submittedAt ) );
            $rows[] = self::tr( 'First Name', esc_html( $firstName ) );
            $rows[] = self::tr( 'Last Name', esc_html( $lastName ) );
            $rows[] = self::tr( 'Full Name', esc_html( $studentName ) );
            $rows[] = self::tr( 'Gender', esc_html( $gender ) );
            $rows[] = self::tr( 'Email Address', esc_html( $studentEmail ) );
            $rows[] = self::tr( 'Phone Number', esc_html( $studentPhone ) );
            $rows[] = self::tr( 'Program of Interest', esc_html( $programInterest ) );

            if ( $programInterest === 'Other' && $programOtherDetails ) {
                $rows[] = self::tr( 'Tell Us More About Your Requirements', nl2br( esc_html( $programOtherDetails ) ) );
            }

            $rows[] = self::tr( 'How Did You Hear About Us', esc_html( $hearAbout ) );

            if ( $hearAbout === 'Other' && $hearOtherSpecify ) {
                $rows[] = self::tr( 'Please Specify', esc_html( $hearOtherSpecify ) );
            }

            if ( $otherRequirements ) {
                $rows[] = self::tr( 'Additional Notes', nl2br( esc_html( $otherRequirements ) ) );
            }

            if ( ! empty( $uploaded_doc_names ) ) {
                $rows[] = self::tr( 'Uploaded Documents', esc_html( implode( ', ', $uploaded_doc_names ) ) );
            }

            if ( ! empty( $google_result['folderName'] ) ) {
                $rows[] = self::tr( 'Folder Name', esc_html( $google_result['folderName'] ) );
            }

            if ( ! empty( $google_result['folderUrl'] ) ) {
                $rows[] = self::tr( 'Folder Link', '<a href="' . esc_url( $google_result['folderUrl'] ) . '" target="_blank">Open Folder</a>' );
            }

            if ( ! empty( $google_result['pdfUrl'] ) ) {
                $rows[] = self::tr( 'Summary PDF Link', '<a href="' . esc_url( $google_result['pdfUrl'] ) . '" target="_blank">Open PDF</a>' );
            }

            $body = '<style>
                body{font-family:Arial,Helvetica,sans-serif;color:#0f172a}
                .wrap{max-width:760px;margin:0 auto;padding:16px}
                h2{margin:0 0 12px;color:#001161}
                table{border-collapse:collapse;width:100%;border:1px solid #e5e7eb}
                th,td{padding:10px 12px;border:1px solid #e5e7eb;vertical-align:top;font-size:14px}
                th{background:#f8fafc;text-align:left;width:240px}
            </style>
            <div class="wrap">
                <h2>Student Inquiry Submission</h2>
                <table>' . implode( '', $rows ) . '</table>
            </div>';

            $headers   = [ 'Content-Type: text/html; charset=UTF-8' ];
            $headers[] = 'From: ' . self::FROM_ALIAS_NAME . ' <' . self::FROM_ALIAS_EMAIL . '>';
            $headers[] = 'Reply-To: ' . $studentName . ' <' . $studentEmail . '>';

            if ( self::MAIL_CC ) {
                $headers[] = 'Cc: ' . self::MAIL_CC;
            }
            if ( self::MAIL_BCC ) {
                $headers[] = 'Bcc: ' . self::MAIL_BCC;
            }

            $sent = wp_mail( self::MAIL_TO, $subject, $body, $headers, $email_attachments );

            foreach ( $email_attachments as $tmp ) {
                @unlink( $tmp );
            }

            if ( ! $sent ) {
                error_log( 'Inquiry email failed, but Google sync succeeded.' );
                wp_send_json_success( [
                    'ok' => true,
                    'warning' => 'Saved to sheet and drive, but email failed.'
                ] );
            }

            wp_send_json_success( [ 'ok' => true ] );

        } catch ( Throwable $e ) {
            error_log( 'canstem_inquiry_request exception: ' . $e->getMessage() );
            error_log( 'canstem_inquiry_request trace: ' . $e->getTraceAsString() );

            wp_send_json_error( [
                'error' => 'PHP exception: ' . $e->getMessage()
            ], 500 );
        }
    }

    private static function post_json( $url, array $payload ) {
        $response = wp_remote_post( $url, [
            'timeout' => 90,
            'headers' => [ 'Content-Type' => 'application/json' ],
            'body'    => wp_json_encode( $payload ),
        ] );

        if ( is_wp_error( $response ) ) {
            error_log( 'Google webhook WP error: ' . $response->get_error_message() );
            return [ 'ok' => false, 'error' => 'WP error: ' . $response->get_error_message() ];
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );

        error_log( 'Google webhook HTTP code: ' . $code );
        error_log( 'Google webhook raw body: ' . $body );

        $json = json_decode( $body, true );

        if ( $code < 200 || $code >= 300 ) {
            return [
                'ok' => false,
                'error' => 'Google webhook returned HTTP ' . $code . ' | Body: ' . substr( trim( wp_strip_all_tags( $body ) ), 0, 300 )
            ];
        }

        if ( ! is_array( $json ) ) {
            return [
                'ok' => false,
                'error' => 'Google webhook did not return valid JSON | Body: ' . substr( trim( wp_strip_all_tags( $body ) ), 0, 300 )
            ];
        }

        return $json;
    }

    private static function tr( $label, $val ) {
        return '<tr><th>' . esc_html( $label ) . '</th><td>' . $val . '</td></tr>';
    }
}

Canstem_Inquiry_Form_Handler::boot();

}
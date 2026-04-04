if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Canstem_Tutoring_Form_Handler' ) ) {

class Canstem_Tutoring_Form_Handler {

    const SMTP_USER        = 'canstem.frontdesk@canstemeducation.com';
    const SMTP_APP_PASS    = 'persoqionuoycbkl';
    const FROM_ALIAS_EMAIL = 'student-request@canstemeducation.com';
    const FROM_ALIAS_NAME  = 'Student Request';

    const MAIL_TO          = 'canstem.frontdesk@canstemeducation.com';
    const MAIL_CC          = '';
    const MAIL_BCC         = '';

    const GOOGLE_WEBHOOK_URL = 'https://script.google.com/macros/s/AKfycbzbJY9G917ZUYdGTEPEdiuBNE7whEv_eEw8P-xjCurb2L_n6tMCTOIyTyEKFu3hnm9GRA/exec';

    public static function boot() {
        add_action( 'phpmailer_init', [ __CLASS__, 'smtp_setup' ], 999 );

        add_action( 'wp_mail_failed', function( $err ) {
            if ( is_wp_error( $err ) ) {
                error_log( 'wp_mail_failed: ' . $err->get_error_message() );
            }
        });

        add_action( 'wp_ajax_canstem_tutoring_request', [ __CLASS__, 'handle_ajax' ] );
        add_action( 'wp_ajax_nopriv_canstem_tutoring_request', [ __CLASS__, 'handle_ajax' ] );
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
                wp_send_json_error( [ 'error' => 'Invalid method.' ], 405 );
            }

            $sx = function( $v ) {
                return sanitize_text_field( wp_unslash( $v ?? '' ) );
            };

            $tx = function( $v ) {
                return sanitize_textarea_field( wp_unslash( $v ?? '' ) );
            };

            $firstName        = $sx( $_POST['firstName'] ?? '' );
            $middleName       = $sx( $_POST['middleName'] ?? '' );
            $lastName         = $sx( $_POST['lastName'] ?? '' );
            $gender           = $sx( $_POST['gender'] ?? '' );
            $dob              = $sx( $_POST['dob'] ?? '' );
            $isAdult          = $sx( $_POST['isAdult'] ?? '' );
            $enrollingGrade   = $sx( $_POST['enrollingGrade'] ?? '' );
            $onlineInClass    = $sx( $_POST['onlineInClass'] ?? '' );
            $studentEmail     = sanitize_email( $_POST['studentEmail'] ?? '' );
            $studentPhone     = $sx( $_POST['studentPhone'] ?? '' );
            $altPhone         = $sx( $_POST['altPhone'] ?? '' );
            $streetAddress    = $sx( $_POST['streetAddress'] ?? '' );
            $cityCountry      = $sx( $_POST['cityCountry'] ?? '' );
            $provinceState    = $sx( $_POST['provinceState'] ?? '' );
            $postalCode       = $sx( $_POST['postalCode'] ?? '' );

            $paymentMethod         = $sx( $_POST['paymentMethod'] ?? '' );
            $cardVerificationMethod = $sx( $_POST['cardVerificationMethod'] ?? '' );
            $cardOrderId           = $sx( $_POST['cardOrderId'] ?? '' );
            $securityQuestion      = $sx( $_POST['securityQuestion'] ?? '' );
            $securityAnswer        = $sx( $_POST['securityAnswer'] ?? '' );
            $interacOrderId        = $sx( $_POST['interacOrderId'] ?? '' );
            $inPersonOrderId       = $sx( $_POST['inPersonOrderId'] ?? '' );
            $internationalOrderId  = $sx( $_POST['internationalOrderId'] ?? '' );

            $subject1             = $sx( $_POST['subject1'] ?? '' );
            $timeSlot1            = $sx( $_POST['timeSlot1'] ?? '' );
            $preferredDays1       = $sx( $_POST['preferredDays1'] ?? '' );
            $specialInstructions1 = $tx( $_POST['specialInstructions1'] ?? '' );
            $anotherCourse1       = $sx( $_POST['anotherCourse1'] ?? '' );

            $subject2             = $sx( $_POST['subject2'] ?? '' );
            $timeSlot2            = $sx( $_POST['timeSlot2'] ?? '' );
            $preferredDays2       = $sx( $_POST['preferredDays2'] ?? '' );
            $specialInstructions2 = $tx( $_POST['specialInstructions2'] ?? '' );
            $anotherCourse2       = $sx( $_POST['anotherCourse2'] ?? '' );

            $subject3             = $sx( $_POST['subject3'] ?? '' );
            $timeSlot3            = $sx( $_POST['timeSlot3'] ?? '' );
            $preferredDays3       = $sx( $_POST['preferredDays3'] ?? '' );
            $specialInstructions3 = $tx( $_POST['specialInstructions3'] ?? '' );
            $anotherCourse3       = $sx( $_POST['anotherCourse3'] ?? '' );

            $subject4             = $sx( $_POST['subject4'] ?? '' );
            $timeSlot4            = $sx( $_POST['timeSlot4'] ?? '' );
            $preferredDays4       = $sx( $_POST['preferredDays4'] ?? '' );
            $specialInstructions4 = $tx( $_POST['specialInstructions4'] ?? '' );

            $hearAbout      = $sx( $_POST['hearAbout'] ?? '' );
            $hearAboutOther = $sx( $_POST['hearAboutOther'] ?? '' );
            $noRefundAgree  = $sx( $_POST['noRefundAgree'] ?? '' );

            if (
                ! $firstName ||
                ! $lastName ||
                ! $gender ||
                ! $dob ||
                ! $isAdult ||
                ! $enrollingGrade ||
                ! $onlineInClass ||
                ! $studentEmail ||
                ! $studentPhone ||
                ! $streetAddress ||
                ! $cityCountry ||
                ! $provinceState ||
                ! $postalCode ||
                ! $paymentMethod ||
                ! $hearAbout ||
                ! $noRefundAgree
            ) {
                wp_send_json_error( [ 'error' => 'Please fill all required fields.' ], 400 );
            }

            if ( ! is_email( $studentEmail ) ) {
                wp_send_json_error( [ 'error' => 'Please enter a valid email address.' ], 400 );
            }

            if ( $hearAbout === 'Other' && ! $hearAboutOther ) {
                wp_send_json_error( [ 'error' => 'Please fill "Please Specify".' ], 400 );
            }

            if ( strpos( $paymentMethod, 'Credit - Debit Card' ) !== false ) {
                if ( ! $cardVerificationMethod ) {
                    wp_send_json_error( [ 'error' => 'Please select a payment verification method.' ], 400 );
                }

                if ( $cardVerificationMethod === 'Paste Order ID' && ! $cardOrderId ) {
                    wp_send_json_error( [ 'error' => 'Please enter the card Order ID.' ], 400 );
                }

                if ( $cardVerificationMethod === 'Upload Receipt Screenshot' && empty( $_FILES['cardReceiptUpload']['name'] ) ) {
                    wp_send_json_error( [ 'error' => 'Please upload the card receipt screenshot.' ], 400 );
                }
            }

            if ( $paymentMethod === 'INTERAC® eTransfer' ) {
                if ( ! $securityQuestion || ! $securityAnswer || ! $interacOrderId ) {
                    wp_send_json_error( [ 'error' => 'Please complete all Interac payment fields.' ], 400 );
                }
            }

            if ( $paymentMethod === 'In-Person' && ! $inPersonOrderId ) {
                wp_send_json_error( [ 'error' => 'Please enter the in-person Order ID.' ], 400 );
            }

            if ( $paymentMethod === 'Paying Internationally' && ! $internationalOrderId ) {
                wp_send_json_error( [ 'error' => 'Please enter the international Order ID.' ], 400 );
            }

            $fullName    = trim( implode( ' ', array_filter( [ $firstName, $middleName, $lastName ] ) ) );
            $submittedAt = current_time( 'mysql' );

            $allowed_exts = [ 'doc', 'docx', 'pdf', 'txt', 'rtf', 'xls', 'xlsx', 'bmp', 'gif', 'jpg', 'jpeg', 'png' ];

            $clean_attachments  = [];
            $email_attachments  = [];
            $uploaded_doc_names = [];

            $uploads = wp_upload_dir();
            $tmpdir  = trailingslashit( $uploads['basedir'] ) . 'canstem-tutoring-temp';

            if ( ! file_exists( $tmpdir ) ) {
                wp_mkdir_p( $tmpdir );
            }

            $file_map = [
                'photoId'           => 'Photo ID',
                'iepDocument'       => 'IEP Accommodation Document',
                'orderIdUpload'     => 'Order ID Upload',
                'reportCard'        => 'Report Card',
                'cardReceiptUpload' => 'Card Receipt Upload',
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

                $mime = sanitize_text_field( $_FILES[ $field ]['type'] ?? 'application/octet-stream' );
                $bin  = file_get_contents( $_FILES[ $field ]['tmp_name'] );

                if ( $bin === false ) {
                    wp_send_json_error( [ 'error' => 'Could not read uploaded file for ' . $label ], 400 );
                }

                $uploaded_doc_names[] = $label;

                $clean_attachments[] = [
                    'label'      => $label,
                    'name'       => $name,
                    'mimeType'   => $mime,
                    'dataBase64' => base64_encode( $bin ),
                ];

                $tmpfile = trailingslashit( $tmpdir ) . wp_unique_filename( $tmpdir, $name );
                if ( file_put_contents( $tmpfile, $bin ) !== false ) {
                    $email_attachments[] = $tmpfile;
                }
            }

            $subject = sprintf(
                'Tutoring Enrollment Submission — %s — %s',
                $fullName,
                $enrollingGrade
            );

            $rows   = [];
            $rows[] = self::tr( 'Timestamp', esc_html( $submittedAt ) );
            $rows[] = self::tr( 'First Name', esc_html( $firstName ) );
            $rows[] = self::tr( 'Middle Name', esc_html( $middleName ) );
            $rows[] = self::tr( 'Last Name', esc_html( $lastName ) );
            $rows[] = self::tr( 'Full Name', esc_html( $fullName ) );
            $rows[] = self::tr( 'Gender', esc_html( $gender ) );
            $rows[] = self::tr( 'Date Of Birth', esc_html( $dob ) );
            $rows[] = self::tr( 'Is Student 18 Or Older', esc_html( $isAdult ) );
            $rows[] = self::tr( 'Enrolling Grade', esc_html( $enrollingGrade ) );
            $rows[] = self::tr( 'Online Or In Class', esc_html( $onlineInClass ) );
            $rows[] = self::tr( 'Student Personal Email Address', esc_html( $studentEmail ) );
            $rows[] = self::tr( 'Student Phone Number', esc_html( $studentPhone ) );
            $rows[] = self::tr( 'Alternative Phone Number', esc_html( $altPhone ) );
            $rows[] = self::tr( 'Street Address', esc_html( $streetAddress ) );
            $rows[] = self::tr( 'City And Country', esc_html( $cityCountry ) );
            $rows[] = self::tr( 'Province State', esc_html( $provinceState ) );
            $rows[] = self::tr( 'Postal Code', esc_html( $postalCode ) );
            $rows[] = self::tr( 'Payment Method', esc_html( $paymentMethod ) );

            if ( $cardVerificationMethod ) {
                $rows[] = self::tr( 'Card Verification Method', esc_html( $cardVerificationMethod ) );
            }
            if ( $cardOrderId ) {
                $rows[] = self::tr( 'Card Order ID', esc_html( $cardOrderId ) );
            }
            if ( $securityQuestion ) {
                $rows[] = self::tr( 'Security Question', esc_html( $securityQuestion ) );
            }
            if ( $securityAnswer ) {
                $rows[] = self::tr( 'Security Answer', esc_html( $securityAnswer ) );
            }
            if ( $interacOrderId ) {
                $rows[] = self::tr( 'Interac Order ID', esc_html( $interacOrderId ) );
            }
            if ( $inPersonOrderId ) {
                $rows[] = self::tr( 'In-Person Order ID', esc_html( $inPersonOrderId ) );
            }
            if ( $internationalOrderId ) {
                $rows[] = self::tr( 'International Order ID', esc_html( $internationalOrderId ) );
            }

            $rows[] = self::tr( 'Subject 1', esc_html( $subject1 ) );
            $rows[] = self::tr( 'Time Slot 1', esc_html( $timeSlot1 ) );
            $rows[] = self::tr( 'Preferred Days 1', esc_html( $preferredDays1 ) );
            $rows[] = self::tr( 'Special Instructions 1', nl2br( esc_html( $specialInstructions1 ) ) );
            $rows[] = self::tr( 'Another Course 1', esc_html( $anotherCourse1 ) );

            if ( $subject2 || $timeSlot2 || $preferredDays2 || $specialInstructions2 || $anotherCourse2 ) {
                $rows[] = self::tr( 'Subject 2', esc_html( $subject2 ) );
                $rows[] = self::tr( 'Time Slot 2', esc_html( $timeSlot2 ) );
                $rows[] = self::tr( 'Preferred Days 2', esc_html( $preferredDays2 ) );
                $rows[] = self::tr( 'Special Instructions 2', nl2br( esc_html( $specialInstructions2 ) ) );
                $rows[] = self::tr( 'Another Course 2', esc_html( $anotherCourse2 ) );
            }

            if ( $subject3 || $timeSlot3 || $preferredDays3 || $specialInstructions3 || $anotherCourse3 ) {
                $rows[] = self::tr( 'Subject 3', esc_html( $subject3 ) );
                $rows[] = self::tr( 'Time Slot 3', esc_html( $timeSlot3 ) );
                $rows[] = self::tr( 'Preferred Days 3', esc_html( $preferredDays3 ) );
                $rows[] = self::tr( 'Special Instructions 3', nl2br( esc_html( $specialInstructions3 ) ) );
                $rows[] = self::tr( 'Another Course 3', esc_html( $anotherCourse3 ) );
            }

            if ( $subject4 || $timeSlot4 || $preferredDays4 || $specialInstructions4 ) {
                $rows[] = self::tr( 'Subject 4', esc_html( $subject4 ) );
                $rows[] = self::tr( 'Time Slot 4', esc_html( $timeSlot4 ) );
                $rows[] = self::tr( 'Preferred Days 4', esc_html( $preferredDays4 ) );
                $rows[] = self::tr( 'Special Instructions 4', nl2br( esc_html( $specialInstructions4 ) ) );
            }

            $rows[] = self::tr( 'How Did You Hear About Us', esc_html( $hearAbout ) );
            if ( $hearAbout === 'Other' ) {
                $rows[] = self::tr( 'Please Specify', esc_html( $hearAboutOther ) );
            }
            $rows[] = self::tr( 'No Refund Agreement', esc_html( $noRefundAgree ) );

            if ( ! empty( $uploaded_doc_names ) ) {
                $rows[] = self::tr( 'Uploaded Documents', esc_html( implode( ', ', $uploaded_doc_names ) ) );
            }

            $body = '<style>
                body{font-family:Arial,Helvetica,sans-serif;color:#0f172a}
                .wrap{max-width:760px;margin:0 auto;padding:16px}
                h2{margin:0 0 12px;color:#001161}
                table{border-collapse:collapse;width:100%;border:1px solid #e5e7eb}
                th,td{padding:10px 12px;border:1px solid #e5e7eb;vertical-align:top;font-size:14px}
                th{background:#f8fafc;text-align:left;width:260px}
            </style>
            <div class="wrap">
                <h2>Tutoring Enrollment Submission</h2>
                <table>' . implode( '', $rows ) . '</table>
            </div>';

            $headers   = [ 'Content-Type: text/html; charset=UTF-8' ];
            $headers[] = 'From: ' . self::FROM_ALIAS_NAME . ' <' . self::FROM_ALIAS_EMAIL . '>';
            $headers[] = 'Reply-To: ' . $fullName . ' <' . $studentEmail . '>';

            if ( self::MAIL_CC ) {
                $headers[] = 'Cc: ' . self::MAIL_CC;
            }
            if ( self::MAIL_BCC ) {
                $headers[] = 'Bcc: ' . self::MAIL_BCC;
            }

            $sent = wp_mail( self::MAIL_TO, $subject, $body, $headers, $email_attachments );

            if ( ! $sent ) {
                foreach ( $email_attachments as $tmp ) {
                    @unlink( $tmp );
                }
                wp_send_json_error( [ 'error' => 'Email notification failed.' ], 500 );
            }

            $google_payload = [
                'submittedAt'             => $submittedAt,
                'firstName'               => $firstName,
                'middleName'              => $middleName,
                'lastName'                => $lastName,
                'fullName'                => $fullName,
                'gender'                  => $gender,
                'dob'                     => $dob,
                'isAdult'                 => $isAdult,
                'enrollingGrade'          => $enrollingGrade,
                'onlineInClass'           => $onlineInClass,
                'studentEmail'            => $studentEmail,
                'studentPhone'            => $studentPhone,
                'altPhone'                => $altPhone,
                'streetAddress'           => $streetAddress,
                'cityCountry'             => $cityCountry,
                'provinceState'           => $provinceState,
                'postalCode'              => $postalCode,
                'paymentMethod'           => $paymentMethod,
                'cardVerificationMethod'  => $cardVerificationMethod,
                'cardOrderId'             => $cardOrderId,
                'securityQuestion'        => $securityQuestion,
                'securityAnswer'          => $securityAnswer,
                'interacOrderId'          => $interacOrderId,
                'inPersonOrderId'         => $inPersonOrderId,
                'internationalOrderId'    => $internationalOrderId,
                'subject1'                => $subject1,
                'timeSlot1'               => $timeSlot1,
                'preferredDays1'          => $preferredDays1,
                'specialInstructions1'    => $specialInstructions1,
                'anotherCourse1'          => $anotherCourse1,
                'subject2'                => $subject2,
                'timeSlot2'               => $timeSlot2,
                'preferredDays2'          => $preferredDays2,
                'specialInstructions2'    => $specialInstructions2,
                'anotherCourse2'          => $anotherCourse2,
                'subject3'                => $subject3,
                'timeSlot3'               => $timeSlot3,
                'preferredDays3'          => $preferredDays3,
                'specialInstructions3'    => $specialInstructions3,
                'anotherCourse3'          => $anotherCourse3,
                'subject4'                => $subject4,
                'timeSlot4'               => $timeSlot4,
                'preferredDays4'          => $preferredDays4,
                'specialInstructions4'    => $specialInstructions4,
                'hearAbout'               => $hearAbout,
                'hearAboutOther'          => $hearAboutOther,
                'noRefundAgree'           => $noRefundAgree,
                'attachments'             => $clean_attachments
            ];

            self::post_json_async( self::GOOGLE_WEBHOOK_URL, $google_payload );

            foreach ( $email_attachments as $tmp ) {
                @unlink( $tmp );
            }

            wp_send_json_success( [ 'ok' => true ] );

        } catch ( Throwable $e ) {
            error_log( 'canstem_tutoring_request exception: ' . $e->getMessage() );
            error_log( 'canstem_tutoring_request trace: ' . $e->getTraceAsString() );

            wp_send_json_error( [
                'error' => 'PHP exception: ' . $e->getMessage()
            ], 500 );
        }
    }

    private static function post_json_async( $url, array $payload ) {
        $response = wp_remote_post( $url, [
            'timeout'  => 1,
            'blocking' => false,
            'headers'  => [ 'Content-Type' => 'application/json' ],
            'body'     => wp_json_encode( $payload ),
        ] );

        if ( is_wp_error( $response ) ) {
            error_log( 'Google webhook async WP error: ' . $response->get_error_message() );
        }
    }

    private static function tr( $label, $val ) {
        return '<tr><th>' . esc_html( $label ) . '</th><td>' . $val . '</td></tr>';
    }
}

Canstem_Tutoring_Form_Handler::boot();

}
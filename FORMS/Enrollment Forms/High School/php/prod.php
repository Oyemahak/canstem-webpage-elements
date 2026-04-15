if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Canstem_Highschool_Form_Handler' ) ) {

class Canstem_Highschool_Form_Handler {

    // =========================
    // CONFIG
    // =========================
    const SMTP_USER        = 'canstem.frontdesk@canstemeducation.com';
    const SMTP_APP_PASS    = 'persoqionuoycbkl';
    const FROM_ALIAS_EMAIL = 'student-request@canstemeducation.com';
    const FROM_ALIAS_NAME  = 'Student Request';

    const MAIL_TO          = 'canstem.frontdesk@canstemeducation.com';
    const MAIL_CC          = '';
    const MAIL_BCC         = '';

    // Replace with your High School Apps Script web app URL
    const GOOGLE_WEBHOOK_URL = 'https://script.google.com/macros/s/AKfycbwEsXmMEZB6Mz7tjzwWeK4HV-eHpU73dWXnsO_2N0gQ2Or_-xiAh2yM6dyXdNCEWuPF/exec';

    public static function boot() {
        add_action( 'phpmailer_init', [ __CLASS__, 'smtp_setup' ], 999 );

        add_action( 'wp_mail_failed', function( $err ) {
            if ( is_wp_error( $err ) ) {
                error_log( 'wp_mail_failed: ' . $err->get_error_message() );
            }
        });

        add_action( 'wp_ajax_canstem_highschool_credit_request', [ __CLASS__, 'handle_ajax' ] );
        add_action( 'wp_ajax_nopriv_canstem_highschool_credit_request', [ __CLASS__, 'handle_ajax' ] );
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

            // =========================
            // HELPERS
            // =========================
            $sx = function( $v ) {
                return sanitize_text_field( wp_unslash( $v ?? '' ) );
            };

            $tx = function( $v ) {
                return sanitize_textarea_field( wp_unslash( $v ?? '' ) );
            };

            $emailx = function( $v ) {
                return sanitize_email( wp_unslash( $v ?? '' ) );
            };

            $course_final = function( $search, $manual ) {
                if ( $search === 'Not in the list' ) {
                    return $manual;
                }
                return $manual ?: $search;
            };

            // =========================
            // STUDENT INFO
            // =========================
            $firstName        = $sx( $_POST['firstName'] ?? '' );
            $middleName       = $sx( $_POST['middleName'] ?? '' );
            $lastName         = $sx( $_POST['lastName'] ?? '' );
            $gender           = $sx( $_POST['gender'] ?? '' );
            $dob              = $sx( $_POST['dob'] ?? '' );
            $isAdult          = $sx( $_POST['isAdult'] ?? '' );
            $onlineInClass    = $sx( $_POST['onlineInClass'] ?? '' );
            $isInternational  = $sx( $_POST['isInternational'] ?? '' );
            $inCanada         = $sx( $_POST['inCanada'] ?? '' );
            $validStudyPermit = $sx( $_POST['validStudyPermit'] ?? '' );
            $moveToCanada     = $sx( $_POST['moveToCanada'] ?? '' );
            $studentEmail     = $emailx( $_POST['studentEmail'] ?? '' );
            $studentPhone     = $sx( $_POST['studentPhone'] ?? '' );

            // =========================
            // ADDRESS
            // =========================
            $streetAddress = $sx( $_POST['streetAddress'] ?? '' );
            $cityCountry   = $sx( $_POST['cityCountry'] ?? '' );
            $provinceState = $sx( $_POST['provinceState'] ?? '' );
            $postalCode    = $sx( $_POST['postalCode'] ?? '' );

            // =========================
            // SCHOOL HISTORY
            // =========================
            $ontarioSchool          = $sx( $_POST['ontarioSchool'] ?? '' );
            $previousSchoolName     = $sx( $_POST['previousSchoolName'] ?? '' );
            $guidanceCounselorEmail = $emailx( $_POST['guidanceCounselorEmail'] ?? '' );
            $institutionName        = $sx( $_POST['institutionName'] ?? '' );
            $institutionEmail       = $emailx( $_POST['institutionEmail'] ?? '' );
            $applicationNumber      = $sx( $_POST['applicationNumber'] ?? '' );

            // =========================
            // COURSES 1 TO 5
            // =========================
            $courseGrade1       = $sx( $_POST['courseGrade1'] ?? '' );
            $courseSearch1      = $sx( $_POST['courseSearch1'] ?? '' );
            $courseManual1      = $sx( $_POST['courseManual1'] ?? '' );
            $courseMode1        = $sx( $_POST['courseMode1'] ?? '' );
            $courseRequirement1 = $tx( $_POST['courseRequirement1'] ?? '' );
            $anotherCourse1     = $sx( $_POST['anotherCourse1'] ?? '' );

            $courseGrade2       = $sx( $_POST['courseGrade2'] ?? '' );
            $courseSearch2      = $sx( $_POST['courseSearch2'] ?? '' );
            $courseManual2      = $sx( $_POST['courseManual2'] ?? '' );
            $courseMode2        = $sx( $_POST['courseMode2'] ?? '' );
            $courseRequirement2 = $tx( $_POST['courseRequirement2'] ?? '' );
            $anotherCourse2     = $sx( $_POST['anotherCourse2'] ?? '' );

            $courseGrade3       = $sx( $_POST['courseGrade3'] ?? '' );
            $courseSearch3      = $sx( $_POST['courseSearch3'] ?? '' );
            $courseManual3      = $sx( $_POST['courseManual3'] ?? '' );
            $courseMode3        = $sx( $_POST['courseMode3'] ?? '' );
            $courseRequirement3 = $tx( $_POST['courseRequirement3'] ?? '' );
            $anotherCourse3     = $sx( $_POST['anotherCourse3'] ?? '' );

            $courseGrade4       = $sx( $_POST['courseGrade4'] ?? '' );
            $courseSearch4      = $sx( $_POST['courseSearch4'] ?? '' );
            $courseManual4      = $sx( $_POST['courseManual4'] ?? '' );
            $courseMode4        = $sx( $_POST['courseMode4'] ?? '' );
            $courseRequirement4 = $tx( $_POST['courseRequirement4'] ?? '' );
            $anotherCourse4     = $sx( $_POST['anotherCourse4'] ?? '' );

            $courseGrade5       = $sx( $_POST['courseGrade5'] ?? '' );
            $courseSearch5      = $sx( $_POST['courseSearch5'] ?? '' );
            $courseManual5      = $sx( $_POST['courseManual5'] ?? '' );
            $courseMode5        = $sx( $_POST['courseMode5'] ?? '' );
            $courseRequirement5 = $tx( $_POST['courseRequirement5'] ?? '' );

            $course1Final = $course_final( $courseSearch1, $courseManual1 );
            $course2Final = $course_final( $courseSearch2, $courseManual2 );
            $course3Final = $course_final( $courseSearch3, $courseManual3 );
            $course4Final = $course_final( $courseSearch4, $courseManual4 );
            $course5Final = $course_final( $courseSearch5, $courseManual5 );

            // =========================
            // CONTACT SECTION
            // =========================
            $parent1FirstName     = $sx( $_POST['parent1FirstName'] ?? '' );
            $parent1LastName      = $sx( $_POST['parent1LastName'] ?? '' );
            $parent1Relationship  = $sx( $_POST['parent1Relationship'] ?? '' );
            $parent1SameAddress   = $sx( $_POST['parent1SameAddress'] ?? '' );
            $parent1StreetAddress = $sx( $_POST['parent1StreetAddress'] ?? '' );
            $parent1CityCountry   = $sx( $_POST['parent1CityCountry'] ?? '' );
            $parent1ProvinceState = $sx( $_POST['parent1ProvinceState'] ?? '' );
            $parent1PostalCode    = $sx( $_POST['parent1PostalCode'] ?? '' );
            $parent1CellPhone     = $sx( $_POST['parent1CellPhone'] ?? '' );
            $parent1Email         = $emailx( $_POST['parent1Email'] ?? '' );

            $addParent2           = $sx( $_POST['addParent2'] ?? '' );

            $parent2FirstName     = $sx( $_POST['parent2FirstName'] ?? '' );
            $parent2LastName      = $sx( $_POST['parent2LastName'] ?? '' );
            $parent2Relationship  = $sx( $_POST['parent2Relationship'] ?? '' );
            $parent2SameAddress   = $sx( $_POST['parent2SameAddress'] ?? '' );
            $parent2StreetAddress = $sx( $_POST['parent2StreetAddress'] ?? '' );
            $parent2CityCountry   = $sx( $_POST['parent2CityCountry'] ?? '' );
            $parent2ProvinceState = $sx( $_POST['parent2ProvinceState'] ?? '' );
            $parent2PostalCode    = $sx( $_POST['parent2PostalCode'] ?? '' );
            $parent2CellPhone     = $sx( $_POST['parent2CellPhone'] ?? '' );
            $parent2Email         = $emailx( $_POST['parent2Email'] ?? '' );

            $addEmergencyContact   = $sx( $_POST['addEmergencyContact'] ?? '' );

            $emergencyFirstName     = $sx( $_POST['emergencyFirstName'] ?? '' );
            $emergencyLastName      = $sx( $_POST['emergencyLastName'] ?? '' );
            $emergencyRelationship  = $sx( $_POST['emergencyRelationship'] ?? '' );
            $emergencyStreetAddress = $sx( $_POST['emergencyStreetAddress'] ?? '' );
            $emergencyCityCountry   = $sx( $_POST['emergencyCityCountry'] ?? '' );
            $emergencyProvinceState = $sx( $_POST['emergencyProvinceState'] ?? '' );
            $emergencyPostalCode    = $sx( $_POST['emergencyPostalCode'] ?? '' );
            $emergencyCellPhone     = $sx( $_POST['emergencyCellPhone'] ?? '' );
            $emergencyEmail         = $emailx( $_POST['emergencyEmail'] ?? '' );

            $emergency2FirstName     = $sx( $_POST['emergency2FirstName'] ?? '' );
            $emergency2LastName      = $sx( $_POST['emergency2LastName'] ?? '' );
            $emergency2Relationship  = $sx( $_POST['emergency2Relationship'] ?? '' );
            $emergency2StreetAddress = $sx( $_POST['emergency2StreetAddress'] ?? '' );
            $emergency2CityCountry   = $sx( $_POST['emergency2CityCountry'] ?? '' );
            $emergency2ProvinceState = $sx( $_POST['emergency2ProvinceState'] ?? '' );
            $emergency2PostalCode    = $sx( $_POST['emergency2PostalCode'] ?? '' );
            $emergency2CellPhone     = $sx( $_POST['emergency2CellPhone'] ?? '' );
            $emergency2Email         = $emailx( $_POST['emergency2Email'] ?? '' );

            // =========================
            // PAYMENT
            // =========================
            $paymentMethod          = $sx( $_POST['paymentMethod'] ?? '' );
            $cardVerificationMethod = $sx( $_POST['cardVerificationMethod'] ?? '' );
            $cardOrderId            = $sx( $_POST['cardOrderId'] ?? '' );
            $securityQuestion       = $sx( $_POST['securityQuestion'] ?? '' );
            $securityAnswer         = $sx( $_POST['securityAnswer'] ?? '' );
            $interacOrderId         = $sx( $_POST['interacOrderId'] ?? '' );
            $internationalOrderId   = $sx( $_POST['internationalOrderId'] ?? '' );

            // =========================
            // OTHER
            // =========================
            $hearAbout          = $sx( $_POST['hearAbout'] ?? '' );
            $hearAboutOther     = $sx( $_POST['hearAboutOther'] ?? '' );
            $termsAgree         = $sx( $_POST['termsAgree'] ?? '' );
            $gradesUploadOption = $sx( $_POST['gradesUploadOption'] ?? '' );
            $noRefundAgree      = $sx( $_POST['noRefundAgree'] ?? '' );

            // Minor signature fields
            $parentTermsAgree = $sx( $_POST['parentTermsAgree'] ?? '' );
            $parentSignature  = $sx( $_POST['parentSignature'] ?? '' );
            $studentSignature = $sx( $_POST['studentSignature'] ?? '' );
            $todayDate        = $sx( $_POST['todayDate'] ?? '' );

            // Adult signature fields
            $applicantTermsAgree = $sx( $_POST['applicantTermsAgree'] ?? '' );
            $adultStudentSignature = $sx( $_POST['adultStudentSignature'] ?? '' );
            $adultTodayDate = $sx( $_POST['adultTodayDate'] ?? '' );

            // =========================
            // VALIDATION
            // =========================
            if (
                ! $firstName ||
                ! $lastName ||
                ! $gender ||
                ! $dob ||
                ! $isAdult ||
                ! $onlineInClass ||
                ! $isInternational ||
                ! $studentEmail ||
                ! $studentPhone ||
                ! $streetAddress ||
                ! $cityCountry ||
                ! $provinceState ||
                ! $postalCode ||
                ! $ontarioSchool ||
                ! $courseGrade1 ||
                ! $course1Final ||
                ! $courseMode1 ||
                ! $paymentMethod ||
                ! $hearAbout ||
                ! $termsAgree ||
                ! $gradesUploadOption ||
                ! $noRefundAgree
            ) {
                wp_send_json_error( [ 'error' => 'Please fill all required fields.' ], 400 );
            }

            if ( ! is_email( $studentEmail ) ) {
                wp_send_json_error( [ 'error' => 'Please enter a valid student email address.' ], 400 );
            }

            if ( $hearAbout === 'Other' && ! $hearAboutOther ) {
                wp_send_json_error( [ 'error' => 'Please specify how you heard about us.' ], 400 );
            }

            if ( $isInternational === 'Yes' ) {
                if ( ! $inCanada ) {
                    wp_send_json_error( [ 'error' => 'Please answer whether you are in Canada.' ], 400 );
                }

                if ( $inCanada === 'Yes' && ! $validStudyPermit ) {
                    wp_send_json_error( [ 'error' => 'Please answer whether you have a valid study permit.' ], 400 );
                }

                if ( $inCanada === 'No' && ! $moveToCanada ) {
                    wp_send_json_error( [ 'error' => 'Please answer whether you are interested in moving to Canada on study permit.' ], 400 );
                }
            }

            if ( $ontarioSchool === 'Yes' && $guidanceCounselorEmail && ! is_email( $guidanceCounselorEmail ) ) {
                wp_send_json_error( [ 'error' => 'Please enter a valid guidance counselor email address.' ], 400 );
            }

            if ( $ontarioSchool === 'No' && $institutionEmail && ! is_email( $institutionEmail ) ) {
                wp_send_json_error( [ 'error' => 'Please enter a valid institution email address.' ], 400 );
            }

            if ( $isAdult === 'No' ) {
                if ( ! $parent1FirstName || ! $parent1LastName || ! $parent1Relationship || ! $parent1SameAddress || ! $parent1CellPhone || ! $parent1Email ) {
                    wp_send_json_error( [ 'error' => 'Please complete Parent/Guardian 1 information.' ], 400 );
                }

                if ( ! is_email( $parent1Email ) ) {
                    wp_send_json_error( [ 'error' => 'Please enter a valid Parent/Guardian 1 email address.' ], 400 );
                }

                if ( $parent1SameAddress === 'No' ) {
                    if ( ! $parent1StreetAddress || ! $parent1CityCountry || ! $parent1ProvinceState || ! $parent1PostalCode ) {
                        wp_send_json_error( [ 'error' => 'Please complete Parent/Guardian 1 address.' ], 400 );
                    }
                }

                if ( ! $addParent2 ) {
                    wp_send_json_error( [ 'error' => "Please answer if you want to add another parent's information." ], 400 );
                }

                if ( $addParent2 === 'Yes' ) {
                    if ( ! $parent2FirstName || ! $parent2LastName || ! $parent2Relationship || ! $parent2SameAddress || ! $parent2CellPhone || ! $parent2Email ) {
                        wp_send_json_error( [ 'error' => 'Please complete Parent/Guardian 2 information.' ], 400 );
                    }

                    if ( ! is_email( $parent2Email ) ) {
                        wp_send_json_error( [ 'error' => 'Please enter a valid Parent/Guardian 2 email address.' ], 400 );
                    }

                    if ( $parent2SameAddress === 'No' ) {
                        if ( ! $parent2StreetAddress || ! $parent2CityCountry || ! $parent2ProvinceState || ! $parent2PostalCode ) {
                            wp_send_json_error( [ 'error' => 'Please complete Parent/Guardian 2 address.' ], 400 );
                        }
                    }
                }

                if ( ! $parentTermsAgree || ! $parentSignature || ! $studentSignature || ! $todayDate ) {
                    wp_send_json_error( [ 'error' => 'Please complete the Parent/Guardian Signature section.' ], 400 );
                }
            }

            if ( $isAdult === 'Yes' ) {
                if ( ! $emergencyFirstName || ! $emergencyLastName || ! $emergencyRelationship || ! $emergencyStreetAddress || ! $emergencyCityCountry || ! $emergencyProvinceState || ! $emergencyPostalCode || ! $emergencyCellPhone || ! $emergencyEmail ) {
                    wp_send_json_error( [ 'error' => 'Please complete Emergency Contact 1 information.' ], 400 );
                }

                if ( ! is_email( $emergencyEmail ) ) {
                    wp_send_json_error( [ 'error' => 'Please enter a valid Emergency Contact 1 email address.' ], 400 );
                }

                if ( ! $addEmergencyContact ) {
                    wp_send_json_error( [ 'error' => 'Please answer if you want to add another emergency contact.' ], 400 );
                }

                if ( $addEmergencyContact === 'Yes' ) {
                    if ( ! $emergency2FirstName || ! $emergency2LastName || ! $emergency2Relationship || ! $emergency2StreetAddress || ! $emergency2CityCountry || ! $emergency2ProvinceState || ! $emergency2PostalCode || ! $emergency2CellPhone || ! $emergency2Email ) {
                        wp_send_json_error( [ 'error' => 'Please complete Emergency Contact 2 information.' ], 400 );
                    }

                    if ( ! is_email( $emergency2Email ) ) {
                        wp_send_json_error( [ 'error' => 'Please enter a valid Emergency Contact 2 email address.' ], 400 );
                    }
                }

                if ( ! $applicantTermsAgree || ! $adultStudentSignature || ! $adultTodayDate ) {
                    wp_send_json_error( [ 'error' => 'Please complete the Applicant Signature section.' ], 400 );
                }
            }

            if ( strpos( $paymentMethod, 'Credit - Debit Card' ) !== false ) {
                if ( ! $cardVerificationMethod ) {
                    wp_send_json_error( [ 'error' => 'Please select a card verification method.' ], 400 );
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

            if ( $paymentMethod === 'Paying Internationally' && ! $internationalOrderId ) {
                wp_send_json_error( [ 'error' => 'Please enter the international Order ID.' ], 400 );
            }

            if ( empty( $_FILES['requiredDocuments']['name'] ) ) {
                wp_send_json_error( [ 'error' => 'Please upload the required transcript/report card/student status document.' ], 400 );
            }

            if ( $isInternational === 'Yes' && $inCanada === 'Yes' && $validStudyPermit === 'Yes' && empty( $_FILES['studyPermitUpload']['name'] ) ) {
                wp_send_json_error( [ 'error' => 'Please upload the valid study permit copy.' ], 400 );
            }

            // =========================
            // FILE UPLOADS
            // =========================
            $allowed_exts = [ 'doc', 'docx', 'pdf', 'txt', 'rtf', 'xls', 'xlsx', 'bmp', 'gif', 'jpg', 'jpeg', 'png', 'webp' ];

            $clean_attachments  = [];
            $email_attachments  = [];
            $uploaded_doc_names = [];

            $uploads = wp_upload_dir();
            $tmpdir  = trailingslashit( $uploads['basedir'] ) . 'canstem-highschool-temp';

            if ( ! file_exists( $tmpdir ) ) {
                wp_mkdir_p( $tmpdir );
            }

            $file_map = [
                'requiredDocuments'   => 'Required Documents',
                'studyPermitUpload'   => 'Study Permit Upload',
                'additionalDocuments1'=> 'Additional Documents 1',
                'additionalDocuments2'=> 'Additional Documents 2',
                'cardReceiptUpload'   => 'Card Receipt Upload',
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

            // =========================
            // EMAIL BUILD
            // =========================
            $fullName    = trim( implode( ' ', array_filter( [ $firstName, $middleName, $lastName ] ) ) );
            $submittedAt = current_time( 'mysql' );

            $subject = sprintf(
                'High School Credit Enrollment — %s',
                $fullName
            );

            $rows   = [];
            $rows[] = self::tr( 'Timestamp', esc_html( $submittedAt ) );
            $rows[] = self::tr( 'First Name', esc_html( $firstName ) );
            $rows[] = self::tr( 'Middle Name', esc_html( $middleName ) );
            $rows[] = self::tr( 'Last Name', esc_html( $lastName ) );
            $rows[] = self::tr( 'Full Name', esc_html( $fullName ) );
            $rows[] = self::tr( 'Gender', esc_html( $gender ) );
            $rows[] = self::tr( 'Date of Birth', esc_html( $dob ) );
            $rows[] = self::tr( 'Is Student 18 Or Older', esc_html( $isAdult ) );
            $rows[] = self::tr( 'Online or In Class', esc_html( $onlineInClass ) );
            $rows[] = self::tr( 'Is International Student', esc_html( $isInternational ) );
            $rows[] = self::tr( 'In Canada', esc_html( $inCanada ) );
            $rows[] = self::tr( 'Valid Study Permit', esc_html( $validStudyPermit ) );
            $rows[] = self::tr( 'Interested in Moving to Canada on Study Permit', esc_html( $moveToCanada ) );
            $rows[] = self::tr( 'Student Email', esc_html( $studentEmail ) );
            $rows[] = self::tr( 'Student Phone', esc_html( $studentPhone ) );

            $rows[] = self::tr( 'Street Address', esc_html( $streetAddress ) );
            $rows[] = self::tr( 'City and Country', esc_html( $cityCountry ) );
            $rows[] = self::tr( 'Province - State', esc_html( $provinceState ) );
            $rows[] = self::tr( 'Postal Code', esc_html( $postalCode ) );

            $rows[] = self::tr( 'Ontario High School History', esc_html( $ontarioSchool ) );
            $rows[] = self::tr( 'Previous School Name', esc_html( $previousSchoolName ) );
            $rows[] = self::tr( 'Guidance Counselor Email', esc_html( $guidanceCounselorEmail ) );
            $rows[] = self::tr( 'Institution Name', esc_html( $institutionName ) );
            $rows[] = self::tr( 'Institution Email', esc_html( $institutionEmail ) );
            $rows[] = self::tr( 'Application Number', esc_html( $applicationNumber ) );

            $rows[] = self::tr( 'Course Grade 1', esc_html( $courseGrade1 ) );
            $rows[] = self::tr( 'Course 1', esc_html( $course1Final ) );
            $rows[] = self::tr( 'Mode 1', esc_html( $courseMode1 ) );
            $rows[] = self::tr( 'Course Requirement 1', nl2br( esc_html( $courseRequirement1 ) ) );
            $rows[] = self::tr( 'Another Course 1', esc_html( $anotherCourse1 ) );

            if ( $courseGrade2 || $course2Final || $courseMode2 ) {
                $rows[] = self::tr( 'Course Grade 2', esc_html( $courseGrade2 ) );
                $rows[] = self::tr( 'Course 2', esc_html( $course2Final ) );
                $rows[] = self::tr( 'Mode 2', esc_html( $courseMode2 ) );
                $rows[] = self::tr( 'Course Requirement 2', nl2br( esc_html( $courseRequirement2 ) ) );
                $rows[] = self::tr( 'Another Course 2', esc_html( $anotherCourse2 ) );
            }

            if ( $courseGrade3 || $course3Final || $courseMode3 ) {
                $rows[] = self::tr( 'Course Grade 3', esc_html( $courseGrade3 ) );
                $rows[] = self::tr( 'Course 3', esc_html( $course3Final ) );
                $rows[] = self::tr( 'Mode 3', esc_html( $courseMode3 ) );
                $rows[] = self::tr( 'Course Requirement 3', nl2br( esc_html( $courseRequirement3 ) ) );
                $rows[] = self::tr( 'Another Course 3', esc_html( $anotherCourse3 ) );
            }

            if ( $courseGrade4 || $course4Final || $courseMode4 ) {
                $rows[] = self::tr( 'Course Grade 4', esc_html( $courseGrade4 ) );
                $rows[] = self::tr( 'Course 4', esc_html( $course4Final ) );
                $rows[] = self::tr( 'Mode 4', esc_html( $courseMode4 ) );
                $rows[] = self::tr( 'Course Requirement 4', nl2br( esc_html( $courseRequirement4 ) ) );
                $rows[] = self::tr( 'Another Course 4', esc_html( $anotherCourse4 ) );
            }

            if ( $courseGrade5 || $course5Final || $courseMode5 ) {
                $rows[] = self::tr( 'Course Grade 5', esc_html( $courseGrade5 ) );
                $rows[] = self::tr( 'Course 5', esc_html( $course5Final ) );
                $rows[] = self::tr( 'Mode 5', esc_html( $courseMode5 ) );
                $rows[] = self::tr( 'Course Requirement 5', nl2br( esc_html( $courseRequirement5 ) ) );
            }

            if ( $isAdult === 'No' ) {
                $rows[] = self::tr( 'Parent 1 First Name', esc_html( $parent1FirstName ) );
                $rows[] = self::tr( 'Parent 1 Last Name', esc_html( $parent1LastName ) );
                $rows[] = self::tr( 'Parent 1 Relationship', esc_html( $parent1Relationship ) );
                $rows[] = self::tr( 'Parent 1 Same Address', esc_html( $parent1SameAddress ) );
                $rows[] = self::tr( 'Parent 1 Street Address', esc_html( $parent1StreetAddress ) );
                $rows[] = self::tr( 'Parent 1 City/Country', esc_html( $parent1CityCountry ) );
                $rows[] = self::tr( 'Parent 1 Province/State', esc_html( $parent1ProvinceState ) );
                $rows[] = self::tr( 'Parent 1 Postal Code', esc_html( $parent1PostalCode ) );
                $rows[] = self::tr( 'Parent 1 Cell Phone', esc_html( $parent1CellPhone ) );
                $rows[] = self::tr( 'Parent 1 Email', esc_html( $parent1Email ) );

                $rows[] = self::tr( 'Add Parent 2', esc_html( $addParent2 ) );
                $rows[] = self::tr( 'Parent 2 First Name', esc_html( $parent2FirstName ) );
                $rows[] = self::tr( 'Parent 2 Last Name', esc_html( $parent2LastName ) );
                $rows[] = self::tr( 'Parent 2 Relationship', esc_html( $parent2Relationship ) );
                $rows[] = self::tr( 'Parent 2 Same Address', esc_html( $parent2SameAddress ) );
                $rows[] = self::tr( 'Parent 2 Street Address', esc_html( $parent2StreetAddress ) );
                $rows[] = self::tr( 'Parent 2 City/Country', esc_html( $parent2CityCountry ) );
                $rows[] = self::tr( 'Parent 2 Province/State', esc_html( $parent2ProvinceState ) );
                $rows[] = self::tr( 'Parent 2 Postal Code', esc_html( $parent2PostalCode ) );
                $rows[] = self::tr( 'Parent 2 Cell Phone', esc_html( $parent2CellPhone ) );
                $rows[] = self::tr( 'Parent 2 Email', esc_html( $parent2Email ) );

                $rows[] = self::tr( 'Parent Terms Agree', esc_html( $parentTermsAgree ) );
                $rows[] = self::tr( 'Parent Signature', esc_html( $parentSignature ) );
                $rows[] = self::tr( 'Student Signature', esc_html( $studentSignature ) );
                $rows[] = self::tr( 'Today Date', esc_html( $todayDate ) );
            }

            if ( $isAdult === 'Yes' ) {
                $rows[] = self::tr( 'Add Emergency Contact', esc_html( $addEmergencyContact ) );
                $rows[] = self::tr( 'Emergency First Name', esc_html( $emergencyFirstName ) );
                $rows[] = self::tr( 'Emergency Last Name', esc_html( $emergencyLastName ) );
                $rows[] = self::tr( 'Emergency Relationship', esc_html( $emergencyRelationship ) );
                $rows[] = self::tr( 'Emergency Street Address', esc_html( $emergencyStreetAddress ) );
                $rows[] = self::tr( 'Emergency City/Country', esc_html( $emergencyCityCountry ) );
                $rows[] = self::tr( 'Emergency Province/State', esc_html( $emergencyProvinceState ) );
                $rows[] = self::tr( 'Emergency Postal Code', esc_html( $emergencyPostalCode ) );
                $rows[] = self::tr( 'Emergency Cell Phone', esc_html( $emergencyCellPhone ) );
                $rows[] = self::tr( 'Emergency Email', esc_html( $emergencyEmail ) );

                $rows[] = self::tr( 'Emergency Contact 2 First Name', esc_html( $emergency2FirstName ) );
                $rows[] = self::tr( 'Emergency Contact 2 Last Name', esc_html( $emergency2LastName ) );
                $rows[] = self::tr( 'Emergency Contact 2 Relationship', esc_html( $emergency2Relationship ) );
                $rows[] = self::tr( 'Emergency Contact 2 Street Address', esc_html( $emergency2StreetAddress ) );
                $rows[] = self::tr( 'Emergency Contact 2 City/Country', esc_html( $emergency2CityCountry ) );
                $rows[] = self::tr( 'Emergency Contact 2 Province/State', esc_html( $emergency2ProvinceState ) );
                $rows[] = self::tr( 'Emergency Contact 2 Postal Code', esc_html( $emergency2PostalCode ) );
                $rows[] = self::tr( 'Emergency Contact 2 Cell Phone', esc_html( $emergency2CellPhone ) );
                $rows[] = self::tr( 'Emergency Contact 2 Email', esc_html( $emergency2Email ) );

                $rows[] = self::tr( 'Applicant Terms Agree', esc_html( $applicantTermsAgree ) );
                $rows[] = self::tr( 'Applicant / Adult Student Signature', esc_html( $adultStudentSignature ) );
                $rows[] = self::tr( 'Applicant Today Date', esc_html( $adultTodayDate ) );
            }

            $rows[] = self::tr( 'Payment Method', esc_html( $paymentMethod ) );
            $rows[] = self::tr( 'Card Verification Method', esc_html( $cardVerificationMethod ) );
            $rows[] = self::tr( 'Card Order ID', esc_html( $cardOrderId ) );
            $rows[] = self::tr( 'Security Question', esc_html( $securityQuestion ) );
            $rows[] = self::tr( 'Security Answer', esc_html( $securityAnswer ) );
            $rows[] = self::tr( 'Interac Order ID', esc_html( $interacOrderId ) );
            $rows[] = self::tr( 'International Order ID', esc_html( $internationalOrderId ) );

            $rows[] = self::tr( 'Hear About', esc_html( $hearAbout ) );
            $rows[] = self::tr( 'Hear About Other', esc_html( $hearAboutOther ) );
            $rows[] = self::tr( 'Terms Agree', esc_html( $termsAgree ) );
            $rows[] = self::tr( 'Grades Upload Option', esc_html( $gradesUploadOption ) );
            $rows[] = self::tr( 'No Refund Agree', esc_html( $noRefundAgree ) );

            if ( ! empty( $uploaded_doc_names ) ) {
                $rows[] = self::tr( 'Uploaded Documents', esc_html( implode( ', ', $uploaded_doc_names ) ) );
            }

            $body = '<style>
                body{font-family:Arial,Helvetica,sans-serif;color:#0f172a}
                .wrap{max-width:900px;margin:0 auto;padding:16px}
                h2{margin:0 0 12px;color:#001161}
                table{border-collapse:collapse;width:100%;border:1px solid #e5e7eb}
                th,td{padding:10px 12px;border:1px solid #e5e7eb;vertical-align:top;font-size:14px}
                th{background:#f8fafc;text-align:left;width:320px}
            </style>
            <div class="wrap">
                <h2>High School Credit Course Enrollment</h2>
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

            // =========================
            // GOOGLE WEBHOOK PAYLOAD
            // =========================
            $google_payload = [
                'submittedAt'             => $submittedAt,
                'firstName'               => $firstName,
                'middleName'              => $middleName,
                'lastName'                => $lastName,
                'fullName'                => $fullName,
                'gender'                  => $gender,
                'dob'                     => $dob,
                'isAdult'                 => $isAdult,
                'onlineInClass'           => $onlineInClass,
                'isInternational'         => $isInternational,
                'inCanada'                => $inCanada,
                'validStudyPermit'        => $validStudyPermit,
                'moveToCanada'            => $moveToCanada,
                'studentEmail'            => $studentEmail,
                'studentPhone'            => $studentPhone,
                'streetAddress'           => $streetAddress,
                'cityCountry'             => $cityCountry,
                'provinceState'           => $provinceState,
                'postalCode'              => $postalCode,
                'ontarioSchool'           => $ontarioSchool,
                'previousSchoolName'      => $previousSchoolName,
                'guidanceCounselorEmail'  => $guidanceCounselorEmail,
                'institutionName'         => $institutionName,
                'institutionEmail'        => $institutionEmail,
                'applicationNumber'       => $applicationNumber,

                'courseGrade1'            => $courseGrade1,
                'courseSearch1'           => $courseSearch1,
                'courseManual1'           => $courseManual1,
                'courseFinal1'            => $course1Final,
                'courseMode1'             => $courseMode1,
                'courseRequirement1'      => $courseRequirement1,
                'anotherCourse1'          => $anotherCourse1,

                'courseGrade2'            => $courseGrade2,
                'courseSearch2'           => $courseSearch2,
                'courseManual2'           => $courseManual2,
                'courseFinal2'            => $course2Final,
                'courseMode2'             => $courseMode2,
                'courseRequirement2'      => $courseRequirement2,
                'anotherCourse2'          => $anotherCourse2,

                'courseGrade3'            => $courseGrade3,
                'courseSearch3'           => $courseSearch3,
                'courseManual3'           => $courseManual3,
                'courseFinal3'            => $course3Final,
                'courseMode3'             => $courseMode3,
                'courseRequirement3'      => $courseRequirement3,
                'anotherCourse3'          => $anotherCourse3,

                'courseGrade4'            => $courseGrade4,
                'courseSearch4'           => $courseSearch4,
                'courseManual4'           => $courseManual4,
                'courseFinal4'            => $course4Final,
                'courseMode4'             => $courseMode4,
                'courseRequirement4'      => $courseRequirement4,
                'anotherCourse4'          => $anotherCourse4,

                'courseGrade5'            => $courseGrade5,
                'courseSearch5'           => $courseSearch5,
                'courseManual5'           => $courseManual5,
                'courseFinal5'            => $course5Final,
                'courseMode5'             => $courseMode5,
                'courseRequirement5'      => $courseRequirement5,

                'parent1FirstName'        => $parent1FirstName,
                'parent1LastName'         => $parent1LastName,
                'parent1Relationship'     => $parent1Relationship,
                'parent1SameAddress'      => $parent1SameAddress,
                'parent1StreetAddress'    => $parent1StreetAddress,
                'parent1CityCountry'      => $parent1CityCountry,
                'parent1ProvinceState'    => $parent1ProvinceState,
                'parent1PostalCode'       => $parent1PostalCode,
                'parent1CellPhone'        => $parent1CellPhone,
                'parent1Email'            => $parent1Email,

                'addParent2'              => $addParent2,
                'parent2FirstName'        => $parent2FirstName,
                'parent2LastName'         => $parent2LastName,
                'parent2Relationship'     => $parent2Relationship,
                'parent2SameAddress'      => $parent2SameAddress,
                'parent2StreetAddress'    => $parent2StreetAddress,
                'parent2CityCountry'      => $parent2CityCountry,
                'parent2ProvinceState'    => $parent2ProvinceState,
                'parent2PostalCode'       => $parent2PostalCode,
                'parent2CellPhone'        => $parent2CellPhone,
                'parent2Email'            => $parent2Email,

                'addEmergencyContact'     => $addEmergencyContact,
                'emergencyFirstName'      => $emergencyFirstName,
                'emergencyLastName'       => $emergencyLastName,
                'emergencyRelationship'   => $emergencyRelationship,
                'emergencyStreetAddress'  => $emergencyStreetAddress,
                'emergencyCityCountry'    => $emergencyCityCountry,
                'emergencyProvinceState'  => $emergencyProvinceState,
                'emergencyPostalCode'     => $emergencyPostalCode,
                'emergencyCellPhone'      => $emergencyCellPhone,
                'emergencyEmail'          => $emergencyEmail,

                'emergency2FirstName'     => $emergency2FirstName,
                'emergency2LastName'      => $emergency2LastName,
                'emergency2Relationship'  => $emergency2Relationship,
                'emergency2StreetAddress' => $emergency2StreetAddress,
                'emergency2CityCountry'   => $emergency2CityCountry,
                'emergency2ProvinceState' => $emergency2ProvinceState,
                'emergency2PostalCode'    => $emergency2PostalCode,
                'emergency2CellPhone'     => $emergency2CellPhone,
                'emergency2Email'         => $emergency2Email,

                'paymentMethod'           => $paymentMethod,
                'cardVerificationMethod'  => $cardVerificationMethod,
                'cardOrderId'             => $cardOrderId,
                'securityQuestion'        => $securityQuestion,
                'securityAnswer'          => $securityAnswer,
                'interacOrderId'          => $interacOrderId,
                'internationalOrderId'    => $internationalOrderId,

                'hearAbout'               => $hearAbout,
                'hearAboutOther'          => $hearAboutOther,
                'termsAgree'              => $termsAgree,
                'gradesUploadOption'      => $gradesUploadOption,
                'noRefundAgree'           => $noRefundAgree,

                'parentTermsAgree'        => $parentTermsAgree,
                'parentSignature'         => $parentSignature,
                'studentSignature'        => $studentSignature,
                'todayDate'               => $todayDate,

                'applicantTermsAgree'     => $applicantTermsAgree,
                'adultStudentSignature'   => $adultStudentSignature,
                'adultTodayDate'          => $adultTodayDate,

                'attachments'             => $clean_attachments
            ];

            self::post_json_async( self::GOOGLE_WEBHOOK_URL, $google_payload );

            foreach ( $email_attachments as $tmp ) {
                @unlink( $tmp );
            }

            wp_send_json_success( [ 'ok' => true ] );

        } catch ( Throwable $e ) {
            error_log( 'canstem_highschool_credit_request exception: ' . $e->getMessage() );
            error_log( 'canstem_highschool_credit_request trace: ' . $e->getTraceAsString() );

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

Canstem_Highschool_Form_Handler::boot();

}
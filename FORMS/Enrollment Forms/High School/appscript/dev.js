const SHEET_ID = '1sOYLYpXd3nYbbui3lu_2vOB1vQGl0AV44HIMBvlwgys';
const SHEET_TAB = 'High School';
const PARENT_FOLDER_ID = '105oG2ZkCgMnt8KvNN7p9u3AwAZnTDcKu';

function doGet() {
  return jsonResponse({
    ok: true,
    message: 'High School Credit Course webhook is live.'
  });
}

function doPost(e) {
  try {
    const data = JSON.parse((e && e.postData && e.postData.contents) || '{}');

    const parent = DriveApp.getFolderById(PARENT_FOLDER_ID);
    const rootFolder = getOrCreateChildFolder(parent, 'High School Credit Course');

    const now = new Date();
    const ts = data.submittedAt || Utilities.formatDate(now, Session.getScriptTimeZone(), 'yyyy-MM-dd HH:mm:ss');
    const fullName = (data.fullName || `${data.firstName || ''} ${data.lastName || ''}`).trim();
    const safeName = sanitizeName(fullName || 'Submission');
    const datePart = Utilities.formatDate(now, Session.getScriptTimeZone(), 'yyyy-MM-dd');

    const enrollmentId = 'HS-' + Utilities.formatDate(now, Session.getScriptTimeZone(), 'yyyyMMdd-HHmmss');
    const folderName = `${safeName} - High School - ${datePart}`;
    const submissionFolder = rootFolder.createFolder(folderName);

    const attachments = Array.isArray(data.attachments) ? data.attachments : [];
    const uploadedDocNames = [];

    attachments.forEach(file => {
      if (!file || !file.dataBase64 || !file.name) return;

      const bytes = Utilities.base64Decode(file.dataBase64);
      const blob = Utilities.newBlob(
        bytes,
        file.mimeType || 'application/octet-stream',
        file.name
      );

      submissionFolder.createFile(blob);
      uploadedDocNames.push(file.label || file.name);
    });

    const pdfUrl = createSummaryPdf(submissionFolder, enrollmentId, data, uploadedDocNames);
    const folderUrl = submissionFolder.getUrl();

    const sheet = SpreadsheetApp.openById(SHEET_ID).getSheetByName(SHEET_TAB);
    if (!sheet) {
      throw new Error(`Sheet tab "${SHEET_TAB}" not found.`);
    }

    const headers = sheet.getRange(1, 1, 1, sheet.getLastColumn()).getValues()[0];
    const rowData = {};

    const course1 = getCourseValue(data, 1);
    const course2 = getCourseValue(data, 2);
    const course3 = getCourseValue(data, 3);
    const course4 = getCourseValue(data, 4);
    const course5 = getCourseValue(data, 5);

    const isAdult = String(data.isAdult || '').trim() === 'Yes';

    // =========================
    // BASIC INFO
    // =========================
    rowData['Timestamp'] = ts;
    rowData['Enrollment ID'] = enrollmentId;
    rowData['First Name'] = data.firstName || '';
    rowData['Middle Name'] = data.middleName || '';
    rowData['Last Name'] = data.lastName || '';
    rowData['Full Name'] = fullName;
    rowData['Gender'] = data.gender || '';
    rowData['Date of Birth'] = data.dob || '';
    rowData['Is Student 18 or Older?'] = data.isAdult || '';
    rowData['Online or In Class?'] = data.onlineInClass || '';
    rowData['Are you an international student?'] = data.isInternational || '';
    rowData['Are you in Canada?'] = data.inCanada || '';
    rowData['Do you have a valid study permit?'] = data.validStudyPermit || '';
    rowData['Are you interested in moving to Canada on study permit?'] = data.moveToCanada || '';
    rowData['Student Personal Email Address'] = data.studentEmail || '';
    rowData['Student Phone Number'] = data.studentPhone || '';

    // =========================
    // ADDRESS
    // =========================
    rowData['Apt / Unit / Street Number / Street Name'] = data.streetAddress || '';
    rowData['City and Country'] = data.cityCountry || '';
    rowData['Province - State'] = data.provinceState || '';
    rowData['Postal Code'] = data.postalCode || '';

    // =========================
    // SCHOOL HISTORY
    // =========================
    rowData['Have you studied in any high school in Ontario?'] = data.ontarioSchool || '';
    rowData['Current or Previous School Name'] = data.previousSchoolName || '';
    rowData['Guidance Counselor Email Address'] = data.guidanceCounselorEmail || '';
    rowData['Institution Name'] = data.institutionName || '';
    rowData['Institution Email Address'] = data.institutionEmail || '';
    rowData['Application Number'] = data.applicationNumber || '';

    // =========================
    // COURSES
    // =========================
    rowData['Course Grade 1'] = data.courseGrade1 || '';
    rowData['Course 1'] = course1;
    rowData['Mode 1'] = data.courseMode1 || '';
    rowData['Course Requirement 1'] = data.courseRequirement1 || '';
    rowData['Another Course 1'] = data.anotherCourse1 || '';

    rowData['Course Grade 2'] = data.courseGrade2 || '';
    rowData['Course 2'] = course2;
    rowData['Mode 2'] = data.courseMode2 || '';
    rowData['Course Requirement 2'] = data.courseRequirement2 || '';
    rowData['Another Course 2'] = data.anotherCourse2 || '';

    rowData['Course Grade 3'] = data.courseGrade3 || '';
    rowData['Course 3'] = course3;
    rowData['Mode 3'] = data.courseMode3 || '';
    rowData['Course Requirement 3'] = data.courseRequirement3 || '';
    rowData['Another Course 3'] = data.anotherCourse3 || '';

    rowData['Course Grade 4'] = data.courseGrade4 || '';
    rowData['Course 4'] = course4;
    rowData['Mode 4'] = data.courseMode4 || '';
    rowData['Course Requirement 4'] = data.courseRequirement4 || '';
    rowData['Another Course 4'] = data.anotherCourse4 || '';

    rowData['Course Grade 5'] = data.courseGrade5 || '';
    rowData['Course 5'] = course5;
    rowData['Mode 5'] = data.courseMode5 || '';
    rowData['Course Requirement 5'] = data.courseRequirement5 || '';

    // =========================
    // PARENT / GUARDIAN 1
    // =========================
    rowData['Parent 1 First Name'] = data.parent1FirstName || '';
    rowData['Parent 1 Last Name'] = data.parent1LastName || '';
    rowData['Parent 1 Relationship'] = data.parent1Relationship || '';
    rowData["Parent 1 Same Address As Student"] = data.parent1SameAddress || '';
    rowData['Parent 1 Street Address'] = data.parent1StreetAddress || '';
    rowData['Parent 1 City and Country'] = data.parent1CityCountry || '';
    rowData['Parent 1 Province - State'] = data.parent1ProvinceState || '';
    rowData['Parent 1 Postal Code'] = data.parent1PostalCode || '';
    rowData['Parent 1 Cell Phone'] = data.parent1CellPhone || '';
    rowData['Parent 1 Email Address'] = data.parent1Email || '';

    // =========================
    // PARENT / GUARDIAN 2
    // =========================
    rowData["Do you want to add another Parent's information?"] = data.addParent2 || '';
    rowData['Parent 2 First Name'] = data.parent2FirstName || '';
    rowData['Parent 2 Last Name'] = data.parent2LastName || '';
    rowData['Parent 2 Relationship'] = data.parent2Relationship || '';
    rowData["Parent 2 Same Address As Student"] = data.parent2SameAddress || '';
    rowData['Parent 2 Street Address'] = data.parent2StreetAddress || '';
    rowData['Parent 2 City and Country'] = data.parent2CityCountry || '';
    rowData['Parent 2 Province - State'] = data.parent2ProvinceState || '';
    rowData['Parent 2 Postal Code'] = data.parent2PostalCode || '';
    rowData['Parent 2 Cell Phone'] = data.parent2CellPhone || '';
    rowData['Parent 2 Email Address'] = data.parent2Email || '';

    // =========================
    // EMERGENCY CONTACT
    // =========================
    rowData['Do you want to add Emergency contact?'] = data.addEmergencyContact || '';
    rowData['Emergency Contact 1 First Name'] = data.emergencyFirstName || '';
    rowData['Emergency Contact 1 Last Name'] = data.emergencyLastName || '';
    rowData['Emergency Contact 1 Relationship'] = data.emergencyRelationship || '';
    rowData['Emergency Contact 1 Street Address'] = data.emergencyStreetAddress || '';
    rowData['Emergency Contact 1 City and Country'] = data.emergencyCityCountry || '';
    rowData['Emergency Contact 1 Province - State'] = data.emergencyProvinceState || '';
    rowData['Emergency Contact 1 Postal Code'] = data.emergencyPostalCode || '';
    rowData['Emergency Contact 1 Cell Phone'] = data.emergencyCellPhone || '';
    rowData['Emergency Contact 1 Email Address'] = data.emergencyEmail || '';

    rowData['Emergency Contact 2 First Name'] = data.emergency2FirstName || '';
    rowData['Emergency Contact 2 Last Name'] = data.emergency2LastName || '';
    rowData['Emergency Contact 2 Relationship'] = data.emergency2Relationship || '';
    rowData['Emergency Contact 2 Street Address'] = data.emergency2StreetAddress || '';
    rowData['Emergency Contact 2 City and Country'] = data.emergency2CityCountry || '';
    rowData['Emergency Contact 2 Province - State'] = data.emergency2ProvinceState || '';
    rowData['Emergency Contact 2 Postal Code'] = data.emergency2PostalCode || '';
    rowData['Emergency Contact 2 Cell Phone'] = data.emergency2CellPhone || '';
    rowData['Emergency Contact 2 Email Address'] = data.emergency2Email || '';

    // =========================
    // PAYMENT
    // =========================
    rowData['Payment Method'] = data.paymentMethod || '';
    rowData['Card Verification Method'] = data.cardVerificationMethod || '';
    rowData['Card Order ID'] = data.cardOrderId || '';
    rowData['Interac Security Question'] = data.securityQuestion || '';
    rowData['Interac Security Answer'] = data.securityAnswer || '';
    rowData['Interac Order ID'] = data.interacOrderId || '';
    rowData['International Order ID'] = data.internationalOrderId || '';

    // =========================
    // OTHER
    // =========================
    rowData['How did you hear about us?'] = data.hearAbout || '';
    rowData['Please Specify'] = data.hearAboutOther || '';
    rowData['Terms Agree'] = data.termsAgree || '';
    rowData['Final Grades Upload Option'] = data.gradesUploadOption || '';
    rowData['No Refund Policy Agreement'] = data.noRefundAgree || '';

    // Minor signature fields
    rowData['Parent Terms Agree'] = isAdult ? '' : (data.parentTermsAgree || '');
    rowData['Parent/Guardian Electronic Signature'] = isAdult ? '' : (data.parentSignature || '');
    rowData['Student Electronic Signature'] = isAdult ? '' : (data.studentSignature || '');
    rowData["Today's Date"] = isAdult ? '' : (data.todayDate || '');

    // Adult signature fields
    rowData["Applicant Terms Agree"] = isAdult ? (data.applicantTermsAgree || '') : '';
    rowData["Applicant / Adult Student Electronic Signature"] = isAdult ? (data.adultStudentSignature || '') : '';
    rowData["Applicant Today's Date"] = isAdult ? (data.adultTodayDate || '') : '';

    // =========================
    // FILE STATUS
    // =========================
    rowData['Required Documents Uploaded'] = hasUploadedLabel(uploadedDocNames, 'Required Documents') ? 'Yes' : 'No';
    rowData['Study Permit Uploaded'] = hasUploadedLabel(uploadedDocNames, 'Study Permit Upload') ? 'Yes' : 'No';
    rowData['Additional Documents 1 Uploaded'] = hasUploadedLabel(uploadedDocNames, 'Additional Documents 1') ? 'Yes' : 'No';
    rowData['Additional Documents 2 Uploaded'] = hasUploadedLabel(uploadedDocNames, 'Additional Documents 2') ? 'Yes' : 'No';
    rowData['Card Receipt Upload Uploaded'] = hasUploadedLabel(uploadedDocNames, 'Card Receipt Upload') ? 'Yes' : 'No';

    // =========================
    // LINKS / STATUS
    // =========================
    rowData['Folder Name'] = folderName;
    rowData['Folder Link'] = folderUrl;
    rowData['Summary PDF Link'] = pdfUrl;
    rowData['Uploaded Documents'] = uploadedDocNames.join(', ');
    rowData['Status'] = 'New';
    rowData['Assigned To'] = '';
    rowData['Notes'] = '';
    rowData['Last Updated'] = ts;

    const row = headers.map(header => rowData[header] !== undefined ? rowData[header] : '');
    sheet.appendRow(row);

    return jsonResponse({
      ok: true,
      enrollmentId,
      folderName,
      folderUrl,
      pdfUrl
    });

  } catch (err) {
    return jsonResponse({
      ok: false,
      error: err.message
    });
  }
}

function getCourseValue(data, index) {
  return (
    data[`courseFinal${index}`] ||
    data[`courseManual${index}`] ||
    data[`courseSearch${index}`] ||
    ''
  );
}

function hasUploadedLabel(uploadedDocNames, label) {
  return uploadedDocNames.indexOf(label) !== -1;
}

function getOrCreateChildFolder(parent, name) {
  const folders = parent.getFoldersByName(name);
  return folders.hasNext() ? folders.next() : parent.createFolder(name);
}

function sanitizeName(value) {
  return String(value || '')
    .replace(/[\\/:*?"<>|#%{}~&]/g, ' ')
    .replace(/\s+/g, ' ')
    .trim();
}

function createSummaryPdf(submissionFolder, enrollmentId, data, uploadedDocNames) {
  const doc = DocumentApp.create(`${enrollmentId} - High School Enrollment Summary`);
  const body = doc.getBody();

  const course1 = getCourseValue(data, 1);
  const course2 = getCourseValue(data, 2);
  const course3 = getCourseValue(data, 3);
  const course4 = getCourseValue(data, 4);
  const course5 = getCourseValue(data, 5);
  const isAdult = String(data.isAdult || '').trim() === 'Yes';

  body.appendParagraph('High School Credit Course Enrollment Submission')
      .setHeading(DocumentApp.ParagraphHeading.HEADING1);

  body.appendParagraph('');

  const rows = [
    ['Timestamp', data.submittedAt || ''],
    ['Enrollment ID', enrollmentId],
    ['First Name', data.firstName || ''],
    ['Middle Name', data.middleName || ''],
    ['Last Name', data.lastName || ''],
    ['Full Name', data.fullName || `${data.firstName || ''} ${data.lastName || ''}`.trim()],
    ['Gender', data.gender || ''],
    ['Date of Birth', data.dob || ''],
    ['Is Student 18 or Older?', data.isAdult || ''],
    ['Online or In Class?', data.onlineInClass || ''],
    ['Are you an international student?', data.isInternational || ''],
    ['Are you in Canada?', data.inCanada || ''],
    ['Do you have a valid study permit?', data.validStudyPermit || ''],
    ['Are you interested in moving to Canada on study permit?', data.moveToCanada || ''],
    ['Student Personal Email Address', data.studentEmail || ''],
    ['Student Phone Number', data.studentPhone || ''],

    ['Apt / Unit / Street Number / Street Name', data.streetAddress || ''],
    ['City and Country', data.cityCountry || ''],
    ['Province - State', data.provinceState || ''],
    ['Postal Code', data.postalCode || ''],

    ['Have you studied in any high school in Ontario?', data.ontarioSchool || ''],
    ['Current or Previous School Name', data.previousSchoolName || ''],
    ['Guidance Counselor Email Address', data.guidanceCounselorEmail || ''],
    ['Institution Name', data.institutionName || ''],
    ['Institution Email Address', data.institutionEmail || ''],
    ['Application Number', data.applicationNumber || ''],

    ['Course Grade 1', data.courseGrade1 || ''],
    ['Course 1', course1],
    ['Mode 1', data.courseMode1 || ''],
    ['Course Requirement 1', data.courseRequirement1 || ''],
    ['Another Course 1', data.anotherCourse1 || ''],

    ['Course Grade 2', data.courseGrade2 || ''],
    ['Course 2', course2],
    ['Mode 2', data.courseMode2 || ''],
    ['Course Requirement 2', data.courseRequirement2 || ''],
    ['Another Course 2', data.anotherCourse2 || ''],

    ['Course Grade 3', data.courseGrade3 || ''],
    ['Course 3', course3],
    ['Mode 3', data.courseMode3 || ''],
    ['Course Requirement 3', data.courseRequirement3 || ''],
    ['Another Course 3', data.anotherCourse3 || ''],

    ['Course Grade 4', data.courseGrade4 || ''],
    ['Course 4', course4],
    ['Mode 4', data.courseMode4 || ''],
    ['Course Requirement 4', data.courseRequirement4 || ''],
    ['Another Course 4', data.anotherCourse4 || ''],

    ['Course Grade 5', data.courseGrade5 || ''],
    ['Course 5', course5],
    ['Mode 5', data.courseMode5 || ''],
    ['Course Requirement 5', data.courseRequirement5 || ''],

    ['Parent 1 First Name', data.parent1FirstName || ''],
    ['Parent 1 Last Name', data.parent1LastName || ''],
    ['Parent 1 Relationship', data.parent1Relationship || ''],
    ['Parent 1 Same Address As Student', data.parent1SameAddress || ''],
    ['Parent 1 Street Address', data.parent1StreetAddress || ''],
    ['Parent 1 City and Country', data.parent1CityCountry || ''],
    ['Parent 1 Province - State', data.parent1ProvinceState || ''],
    ['Parent 1 Postal Code', data.parent1PostalCode || ''],
    ['Parent 1 Cell Phone', data.parent1CellPhone || ''],
    ['Parent 1 Email Address', data.parent1Email || ''],

    ["Do you want to add another Parent's information?", data.addParent2 || ''],
    ['Parent 2 First Name', data.parent2FirstName || ''],
    ['Parent 2 Last Name', data.parent2LastName || ''],
    ['Parent 2 Relationship', data.parent2Relationship || ''],
    ['Parent 2 Same Address As Student', data.parent2SameAddress || ''],
    ['Parent 2 Street Address', data.parent2StreetAddress || ''],
    ['Parent 2 City and Country', data.parent2CityCountry || ''],
    ['Parent 2 Province - State', data.parent2ProvinceState || ''],
    ['Parent 2 Postal Code', data.parent2PostalCode || ''],
    ['Parent 2 Cell Phone', data.parent2CellPhone || ''],
    ['Parent 2 Email Address', data.parent2Email || ''],

    ['Do you want to add Emergency contact?', data.addEmergencyContact || ''],
    ['Emergency Contact 1 First Name', data.emergencyFirstName || ''],
    ['Emergency Contact 1 Last Name', data.emergencyLastName || ''],
    ['Emergency Contact 1 Relationship', data.emergencyRelationship || ''],
    ['Emergency Contact 1 Street Address', data.emergencyStreetAddress || ''],
    ['Emergency Contact 1 City and Country', data.emergencyCityCountry || ''],
    ['Emergency Contact 1 Province - State', data.emergencyProvinceState || ''],
    ['Emergency Contact 1 Postal Code', data.emergencyPostalCode || ''],
    ['Emergency Contact 1 Cell Phone', data.emergencyCellPhone || ''],
    ['Emergency Contact 1 Email Address', data.emergencyEmail || ''],

    ['Emergency Contact 2 First Name', data.emergency2FirstName || ''],
    ['Emergency Contact 2 Last Name', data.emergency2LastName || ''],
    ['Emergency Contact 2 Relationship', data.emergency2Relationship || ''],
    ['Emergency Contact 2 Street Address', data.emergency2StreetAddress || ''],
    ['Emergency Contact 2 City and Country', data.emergency2CityCountry || ''],
    ['Emergency Contact 2 Province - State', data.emergency2ProvinceState || ''],
    ['Emergency Contact 2 Postal Code', data.emergency2PostalCode || ''],
    ['Emergency Contact 2 Cell Phone', data.emergency2CellPhone || ''],
    ['Emergency Contact 2 Email Address', data.emergency2Email || ''],

    ['Payment Method', data.paymentMethod || ''],
    ['Card Verification Method', data.cardVerificationMethod || ''],
    ['Card Order ID', data.cardOrderId || ''],
    ['Interac Security Question', data.securityQuestion || ''],
    ['Interac Security Answer', data.securityAnswer || ''],
    ['Interac Order ID', data.interacOrderId || ''],
    ['International Order ID', data.internationalOrderId || ''],

    ['How did you hear about us?', data.hearAbout || ''],
    ['Please Specify', data.hearAboutOther || ''],
    ['Terms Agree', data.termsAgree || ''],
    ['Final Grades Upload Option', data.gradesUploadOption || ''],
    ['No Refund Policy Agreement', data.noRefundAgree || ''],

    ['Parent Terms Agree', isAdult ? '' : (data.parentTermsAgree || '')],
    ['Parent/Guardian Electronic Signature', isAdult ? '' : (data.parentSignature || '')],
    ['Student Electronic Signature', isAdult ? '' : (data.studentSignature || '')],
    ["Today's Date", isAdult ? '' : (data.todayDate || '')],

    ['Applicant Terms Agree', isAdult ? (data.applicantTermsAgree || '') : ''],
    ['Applicant / Adult Student Electronic Signature', isAdult ? (data.adultStudentSignature || '') : ''],
    ["Applicant Today's Date", isAdult ? (data.adultTodayDate || '') : '']
  ];

  rows.forEach(([label, value]) => {
    body.appendParagraph(`${label}: ${value || ''}`);
  });

  body.appendParagraph('');
  body.appendParagraph('Uploaded Documents:');

  if (uploadedDocNames.length) {
    uploadedDocNames.forEach(name => body.appendParagraph(name));
  } else {
    body.appendParagraph('No uploaded documents.');
  }

  doc.saveAndClose();

  const docFile = DriveApp.getFileById(doc.getId());
  const pdfBlob = docFile.getBlob().setName(`${enrollmentId} - High School Enrollment Summary.pdf`);
  const pdfFile = submissionFolder.createFile(pdfBlob);

  docFile.setTrashed(true);

  return pdfFile.getUrl();
}

function jsonResponse(obj) {
  return ContentService
    .createTextOutput(JSON.stringify(obj))
    .setMimeType(ContentService.MimeType.JSON);
}
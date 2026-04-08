const SHEET_ID = '1sOYLYpXd3nYbbui3lu_2vOB1vQGl0AV44HIMBvlwgys';
const SHEET_TAB = 'Tutoring';
const PARENT_FOLDER_ID = '105oG2ZkCgMnt8KvNN7p9u3AwAZnTDcKu';

function doGet() {
  return jsonResponse({
    ok: true,
    message: 'Tutoring webhook is live.'
  });
}

function doPost(e) {
  try {
    const data = JSON.parse((e && e.postData && e.postData.contents) || '{}');

    const parent = DriveApp.getFolderById(PARENT_FOLDER_ID);
    const tutoringRoot = getOrCreateChildFolder(parent, 'Tutoring');

    const ts = data.submittedAt || Utilities.formatDate(new Date(), Session.getScriptTimeZone(), 'yyyy-MM-dd HH:mm:ss');
    const fullName = (data.fullName || `${data.firstName || ''} ${data.middleName || ''} ${data.lastName || ''}`)
      .replace(/\s+/g, ' ')
      .trim();

    const safeName = sanitizeName(fullName || 'Submission');
    const safeGrade = sanitizeName(data.enrollingGrade || 'Tutoring');
    const datePart = Utilities.formatDate(new Date(), Session.getScriptTimeZone(), 'yyyy-MM-dd');

    const folderName = `${safeName} - ${safeGrade} - ${datePart}`;
    const submissionFolder = tutoringRoot.createFolder(folderName);

    const enrollmentId = 'TUT-' + Utilities.formatDate(new Date(), Session.getScriptTimeZone(), 'yyyyMMdd-HHmmss');

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

    createSummaryPdf(submissionFolder, enrollmentId, data, uploadedDocNames);
    const folderUrl = submissionFolder.getUrl();

    const sheet = SpreadsheetApp.openById(SHEET_ID).getSheetByName(SHEET_TAB);
    if (!sheet) {
      throw new Error(`Sheet tab "${SHEET_TAB}" not found.`);
    }

    const headers = sheet.getRange(1, 1, 1, sheet.getLastColumn()).getValues()[0];
    const rowData = {};

    rowData['Timestamp'] = ts;
    rowData['Enrollment ID'] = enrollmentId;
    rowData['First Name'] = data.firstName || '';
    rowData['Middle Name'] = data.middleName || '';
    rowData['Last Name'] = data.lastName || '';
    rowData['Full Name'] = fullName;
    rowData['Gender'] = data.gender || '';
    rowData['Date of Birth'] = data.dob || '';
    rowData['Is Student 18 or Older?'] = data.isAdult || '';
    rowData['Enrolling Grade'] = data.enrollingGrade || '';
    rowData['Online or In Class?'] = data.onlineInClass || '';
    rowData['Student Personal Email Address'] = data.studentEmail || '';
    rowData['Student Phone Number'] = data.studentPhone || '';
    rowData['Alternative Phone Number'] = data.altPhone || '';
    rowData['Apt / Unit / Street Number / Street Name'] = data.streetAddress || '';
    rowData['City and Country'] = data.cityCountry || '';
    rowData['Province - State'] = data.provinceState || '';
    rowData['Postal Code'] = data.postalCode || '';

    rowData['Payment Method'] = data.paymentMethod || '';
    rowData['Card Verification Method'] = data.cardVerificationMethod || '';
    rowData['Card Order ID'] = data.cardOrderId || '';
    rowData['Card Receipt Screenshot Uploaded'] = hasUploadedLabel(uploadedDocNames, 'Card Receipt Upload') ? 'Yes' : 'No';
    rowData['Security Question'] = data.securityQuestion || '';
    rowData['Security Answer'] = data.securityAnswer || '';
    rowData['Interac Order ID'] = data.interacOrderId || '';
    rowData['International Order ID'] = data.internationalOrderId || '';

    rowData['Subject 1'] = data.subject1 || '';
    rowData['Time Slot 1'] = data.timeSlot1 || '';
    rowData['Preferred Days 1'] = data.preferredDays1 || '';
    rowData['Special Instructions 1'] = data.specialInstructions1 || '';
    rowData['Another Course 1'] = data.anotherCourse1 || '';

    rowData['Subject 2'] = data.subject2 || '';
    rowData['Time Slot 2'] = data.timeSlot2 || '';
    rowData['Preferred Days 2'] = data.preferredDays2 || '';
    rowData['Special Instructions 2'] = data.specialInstructions2 || '';
    rowData['Another Course 2'] = data.anotherCourse2 || '';

    rowData['Subject 3'] = data.subject3 || '';
    rowData['Time Slot 3'] = data.timeSlot3 || '';
    rowData['Preferred Days 3'] = data.preferredDays3 || '';
    rowData['Special Instructions 3'] = data.specialInstructions3 || '';
    rowData['Another Course 3'] = data.anotherCourse3 || '';

    rowData['Subject 4'] = data.subject4 || '';
    rowData['Time Slot 4'] = data.timeSlot4 || '';
    rowData['Preferred Days 4'] = data.preferredDays4 || '';
    rowData['Special Instructions 4'] = data.specialInstructions4 || '';

    rowData['Photo ID Uploaded'] = hasUploadedLabel(uploadedDocNames, 'Photo ID') ? 'Yes' : 'No';
    rowData['IEP Accommodation Document Uploaded'] = hasUploadedLabel(uploadedDocNames, 'IEP Accommodation Document') ? 'Yes' : 'No';
    rowData['Order ID Upload Uploaded'] = hasUploadedLabel(uploadedDocNames, 'Order ID Upload') ? 'Yes' : 'No';
    rowData['Report Card Uploaded'] = hasUploadedLabel(uploadedDocNames, 'Report Card') ? 'Yes' : 'No';

    rowData['How did you hear about us?'] = data.hearAbout || '';
    rowData['Please Specify'] = data.hearAboutOther || '';
    rowData['No Refund Policy Agreement'] = data.noRefundAgree || '';

    rowData['Folder Name'] = folderName;
    rowData['Folder Link'] = folderUrl;
    rowData['Uploaded Documents'] = uploadedDocNames.join(', ');
    rowData['Status'] = 'New';
    rowData['Assigned To'] = '';
    rowData['Notes'] = '';
    rowData['Last Updated'] = 'New';

    const row = headers.map(header => rowData[header] !== undefined ? rowData[header] : '');
    sheet.appendRow(row);

    return jsonResponse({
      ok: true,
      enrollmentId,
      folderName,
      folderUrl
    });

  } catch (err) {
    return jsonResponse({
      ok: false,
      error: err.message
    });
  }
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
  const doc = DocumentApp.create(`${enrollmentId} - Tutoring Summary`);
  const body = doc.getBody();

  body.appendParagraph('Tutoring Enrollment Form Submission')
      .setHeading(DocumentApp.ParagraphHeading.HEADING1);

  body.appendParagraph('');

  const rows = [
    ['Timestamp', data.submittedAt || ''],
    ['Enrollment ID', enrollmentId],
    ['First Name', data.firstName || ''],
    ['Middle Name', data.middleName || ''],
    ['Last Name', data.lastName || ''],
    ['Full Name', data.fullName || `${data.firstName || ''} ${data.middleName || ''} ${data.lastName || ''}`.replace(/\s+/g, ' ').trim()],
    ['Gender', data.gender || ''],
    ['Date of Birth', data.dob || ''],
    ['Is Student 18 or Older?', data.isAdult || ''],
    ['Enrolling Grade', data.enrollingGrade || ''],
    ['Online or In Class?', data.onlineInClass || ''],
    ['Student Personal Email Address', data.studentEmail || ''],
    ['Student Phone Number', data.studentPhone || ''],
    ['Alternative Phone Number', data.altPhone || ''],
    ['Apt / Unit / Street Number / Street Name', data.streetAddress || ''],
    ['City and Country', data.cityCountry || ''],
    ['Province - State', data.provinceState || ''],
    ['Postal Code', data.postalCode || ''],
    ['Payment Method', data.paymentMethod || ''],
    ['Card Verification Method', data.cardVerificationMethod || ''],
    ['Card Order ID', data.cardOrderId || ''],
    ['Security Question', data.securityQuestion || ''],
    ['Security Answer', data.securityAnswer || ''],
    ['Interac Order ID', data.interacOrderId || ''],
    ['International Order ID', data.internationalOrderId || ''],
    ['Subject 1', data.subject1 || ''],
    ['Time Slot 1', data.timeSlot1 || ''],
    ['Preferred Days 1', data.preferredDays1 || ''],
    ['Special Instructions 1', data.specialInstructions1 || ''],
    ['Another Course 1', data.anotherCourse1 || ''],
    ['Subject 2', data.subject2 || ''],
    ['Time Slot 2', data.timeSlot2 || ''],
    ['Preferred Days 2', data.preferredDays2 || ''],
    ['Special Instructions 2', data.specialInstructions2 || ''],
    ['Another Course 2', data.anotherCourse2 || ''],
    ['Subject 3', data.subject3 || ''],
    ['Time Slot 3', data.timeSlot3 || ''],
    ['Preferred Days 3', data.preferredDays3 || ''],
    ['Special Instructions 3', data.specialInstructions3 || ''],
    ['Another Course 3', data.anotherCourse3 || ''],
    ['Subject 4', data.subject4 || ''],
    ['Time Slot 4', data.timeSlot4 || ''],
    ['Preferred Days 4', data.preferredDays4 || ''],
    ['Special Instructions 4', data.specialInstructions4 || ''],
    ['How did you hear about us?', data.hearAbout || ''],
    ['Please Specify', data.hearAboutOther || ''],
    ['No Refund Policy Agreement', data.noRefundAgree || '']
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
  const pdfBlob = docFile.getBlob().setName(`${enrollmentId} - Tutoring Summary.pdf`);
  submissionFolder.createFile(pdfBlob);

  docFile.setTrashed(true);
}

function jsonResponse(obj) {
  return ContentService
    .createTextOutput(JSON.stringify(obj))
    .setMimeType(ContentService.MimeType.JSON);
}
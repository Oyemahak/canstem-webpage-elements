const SHEET_ID = '1sOYLYpXd3nYbbui3lu_2vOB1vQGl0AV44HIMBvlwgys';
const SHEET_TAB = 'Tutoring';
const PARENT_FOLDER_ID = '105oG2ZkCgMnt8KvNN7p9u3AwAZnTDcKu';

/*
Expected sheet headers in row 1:

Timestamp
Form Type
Form Title
Submission ID
First Name
Middle Name
Last Name
Full Name
Gender
Date Of Birth
Is Student 18 Or Older
Enrolling Grade
Online Or In Class
Student Personal Email Address
Student Phone Number
Alternative Phone Number
Street Address
City And Country
Province State
Postal Code
Payment Method
Card Verification Method
Card Order ID
Interac Security Question
Interac Security Answer
Interac Order ID
International Order ID
Subject 1
Time Slot 1
Preferred Days 1
Special Instructions 1
Another Course 1
Subject 2
Time Slot 2
Preferred Days 2
Special Instructions 2
Another Course 2
Subject 3
Time Slot 3
Preferred Days 3
Special Instructions 3
Another Course 3
Subject 4
Time Slot 4
Preferred Days 4
Special Instructions 4
Photo ID Uploaded
IEP Accommodation Document Uploaded
Order ID Upload Uploaded
Report Card Uploaded
Card Receipt Upload Uploaded
How Did You Hear About Us
Please Specify
No Refund Agreement
Uploaded Documents
Folder Name
Folder Link
Summary PDF Link
Status
Assigned To
Notes
Last Updated
*/

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
    const fullName = (data.fullName || `${data.firstName || ''} ${data.lastName || ''}`).trim();
    const safeName = sanitizeName(fullName || 'Submission');
    const safeGrade = sanitizeName(data.enrollingGrade || 'Tutoring');
    const datePart = Utilities.formatDate(new Date(), Session.getScriptTimeZone(), 'yyyy-MM-dd');

    const folderName = `${safeName} - ${safeGrade} - ${datePart}`;
    const submissionFolder = tutoringRoot.createFolder(folderName);

    const submissionId = 'TUT-' + Utilities.formatDate(new Date(), Session.getScriptTimeZone(), 'yyyyMMdd-HHmmss');

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

    const pdfUrl = createSummaryPdf(submissionFolder, submissionId, data, uploadedDocNames);
    const folderUrl = submissionFolder.getUrl();

    const sheet = SpreadsheetApp.openById(SHEET_ID).getSheetByName(SHEET_TAB);
    if (!sheet) {
      throw new Error(`Sheet tab "${SHEET_TAB}" not found.`);
    }

    const headers = sheet.getRange(1, 1, 1, sheet.getLastColumn()).getValues()[0];
    const rowData = {};

    rowData['Timestamp'] = ts;
    rowData['Form Type'] = 'Tutoring';
    rowData['Form Title'] = 'Tutoring Enrollment Form';
    rowData['Submission ID'] = submissionId;

    rowData['First Name'] = data.firstName || '';
    rowData['Middle Name'] = data.middleName || '';
    rowData['Last Name'] = data.lastName || '';
    rowData['Full Name'] = fullName;
    rowData['Gender'] = data.gender || '';
    rowData['Date Of Birth'] = data.dob || '';
    rowData['Is Student 18 Or Older'] = data.isAdult || '';
    rowData['Enrolling Grade'] = data.enrollingGrade || '';
    rowData['Online Or In Class'] = data.onlineInClass || '';
    rowData['Student Personal Email Address'] = data.studentEmail || '';
    rowData['Student Phone Number'] = data.studentPhone || '';
    rowData['Alternative Phone Number'] = data.altPhone || '';
    rowData['Street Address'] = data.streetAddress || '';
    rowData['City And Country'] = data.cityCountry || '';
    rowData['Province State'] = data.provinceState || '';
    rowData['Postal Code'] = data.postalCode || '';

    rowData['Payment Method'] = data.paymentMethod || '';
    rowData['Card Verification Method'] = data.cardVerificationMethod || '';
    rowData['Card Order ID'] = data.cardOrderId || '';
    rowData['Interac Security Question'] = data.securityQuestion || '';
    rowData['Interac Security Answer'] = data.securityAnswer || '';
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
    rowData['Card Receipt Upload Uploaded'] = hasUploadedLabel(uploadedDocNames, 'Card Receipt Upload') ? 'Yes' : 'No';

    rowData['How Did You Hear About Us'] = data.hearAbout || '';
    rowData['Please Specify'] = data.hearAboutOther || '';
    rowData['No Refund Agreement'] = data.noRefundAgree || '';

    rowData['Uploaded Documents'] = uploadedDocNames.join(', ');
    rowData['Folder Name'] = folderName;
    rowData['Folder Link'] = folderUrl;
    rowData['Summary PDF Link'] = pdfUrl;
    rowData['Status'] = 'New';
    rowData['Assigned To'] = '';
    rowData['Notes'] = '';
    rowData['Last Updated'] = 'New';

    const row = headers.map(header => rowData[header] !== undefined ? rowData[header] : '');
    sheet.appendRow(row);

    return jsonResponse({
      ok: true,
      submissionId,
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

function createSummaryPdf(submissionFolder, submissionId, data, uploadedDocNames) {
  const doc = DocumentApp.create(`${submissionId} - Tutoring Summary`);
  const body = doc.getBody();

  body.appendParagraph('Tutoring Enrollment Form Submission')
      .setHeading(DocumentApp.ParagraphHeading.HEADING1);

  body.appendParagraph('');

  const rows = [
    ['Timestamp', data.submittedAt || ''],
    ['Submission ID', submissionId],
    ['First Name', data.firstName || ''],
    ['Middle Name', data.middleName || ''],
    ['Last Name', data.lastName || ''],
    ['Full Name', data.fullName || `${data.firstName || ''} ${data.lastName || ''}`.trim()],
    ['Gender', data.gender || ''],
    ['Date Of Birth', data.dob || ''],
    ['Is Student 18 Or Older', data.isAdult || ''],
    ['Enrolling Grade', data.enrollingGrade || ''],
    ['Online Or In Class', data.onlineInClass || ''],
    ['Student Personal Email Address', data.studentEmail || ''],
    ['Student Phone Number', data.studentPhone || ''],
    ['Alternative Phone Number', data.altPhone || ''],
    ['Street Address', data.streetAddress || ''],
    ['City And Country', data.cityCountry || ''],
    ['Province State', data.provinceState || ''],
    ['Postal Code', data.postalCode || ''],
    ['Payment Method', data.paymentMethod || ''],
    ['Card Verification Method', data.cardVerificationMethod || ''],
    ['Card Order ID', data.cardOrderId || ''],
    ['Interac Security Question', data.securityQuestion || ''],
    ['Interac Security Answer', data.securityAnswer || ''],
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
    ['How Did You Hear About Us', data.hearAbout || ''],
    ['Please Specify', data.hearAboutOther || ''],
    ['No Refund Agreement', data.noRefundAgree || '']
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
  const pdfBlob = docFile.getBlob().setName(`${submissionId} - Tutoring Summary.pdf`);
  const pdfFile = submissionFolder.createFile(pdfBlob);

  docFile.setTrashed(true);

  return pdfFile.getUrl();
}

function jsonResponse(obj) {
  return ContentService
    .createTextOutput(JSON.stringify(obj))
    .setMimeType(ContentService.MimeType.JSON);
}
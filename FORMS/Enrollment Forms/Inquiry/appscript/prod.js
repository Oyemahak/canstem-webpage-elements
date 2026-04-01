const SHEET_ID = '1sOYLYpXd3nYbbui3lu_2vOB1vQGl0AV44HIMBvlwgys';
const SHEET_TAB = 'All Inquiries';
const PARENT_FOLDER_ID = '105oG2ZkCgMnt8KvNN7p9u3AwAZnTDcKu';

function doPost(e) {
  try {
    const data = JSON.parse(e.postData.contents || '{}');

    const parent = DriveApp.getFolderById(PARENT_FOLDER_ID);
    const inquiryRoot = getOrCreateChildFolder(parent, 'Inquiry');

    const ts = data.submittedAt || Utilities.formatDate(new Date(), Session.getScriptTimeZone(), 'yyyy-MM-dd HH:mm:ss');
    const fullName = (data.fullName || `${data.firstName || ''} ${data.lastName || ''}`).trim();
    const safeProgram = sanitizeName(data.programInterest || 'Inquiry');
    const safeName = sanitizeName(fullName || 'Submission');
    const datePart = Utilities.formatDate(new Date(), Session.getScriptTimeZone(), 'yyyy-MM-dd');

    const folderName = `${safeName} - ${safeProgram} - ${datePart}`;
    const submissionFolder = inquiryRoot.createFolder(folderName);

    const inquiryId = 'INQ-' + Utilities.formatDate(new Date(), Session.getScriptTimeZone(), 'yyyyMMdd-HHmmss');

    const fileLinks = {};
    const uploadedDocNames = [];

    const attachments = Array.isArray(data.attachments) ? data.attachments : [];
    attachments.forEach(file => {
      if (!file || !file.dataBase64 || !file.name) return;

      const bytes = Utilities.base64Decode(file.dataBase64);
      const blob = Utilities.newBlob(
        bytes,
        file.mimeType || 'application/octet-stream',
        file.name
      );
      const created = submissionFolder.createFile(blob);

      fileLinks[file.label || file.name] = created.getUrl();
      uploadedDocNames.push(file.label || file.name);
    });

    const pdfUrl = createSummaryPdf(submissionFolder, inquiryId, data, fileLinks);
    const folderUrl = submissionFolder.getUrl();

    const sheet = SpreadsheetApp.openById(SHEET_ID).getSheetByName(SHEET_TAB);
    if (!sheet) throw new Error(`Sheet tab "${SHEET_TAB}" not found.`);

    const row = [
      ts,                                              // Timestamp
      inquiryId,                                       // Inquiry ID
      data.firstName || '',                            // First Name
      data.lastName || '',                             // Last Name
      fullName,                                        // Full Name
      data.studentEmail || '',                         // Email Address
      data.studentPhone || '',                         // Phone Number
      data.programInterest || '',                      // Program of Interest
      data.hearAbout || '',                            // How Did You Hear About Us
      data.hearOtherSpecify || '',                     // Please Specify
      data.programOtherDetails || '',                  // Tell Us More About Your Requirements
      uploadedDocNames.join(', '),                     // Uploaded Documents
      folderUrl,                                       // Submission Folder Link
      pdfUrl,                                          // PDF Summary Link
      fileLinks['Transcript 1 - Report card'] || '',   // Transcript Link
      fileLinks['Additional Document 1'] || '',        // Additional Document Link
      fileLinks['Picture ID Passport'] || '',          // Picture ID Link
      'New',                                           // Status
      '',                                              // Assigned To
      '',                                              // Notes
      '',                                              // Follow Up Date
      '',                                              // Payment Status
      ''                                               // Internal Remarks
    ];

    sheet.appendRow(row);

    return jsonResponse({
      ok: true,
      inquiryId,
      folderUrl,
      pdfUrl,
      fileLinks
    });

  } catch (err) {
    return jsonResponse({
      ok: false,
      error: err.message
    });
  }
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

function createSummaryPdf(submissionFolder, inquiryId, data, fileLinks) {
  const doc = DocumentApp.create(`${inquiryId} - Inquiry Summary`);
  const body = doc.getBody();

  body.appendParagraph('Student Inquiry Submission')
      .setHeading(DocumentApp.ParagraphHeading.HEADING1);

  body.appendParagraph('');

  const rows = [
    ['Timestamp', data.submittedAt || ''],
    ['Inquiry ID', inquiryId],
    ['First Name', data.firstName || ''],
    ['Last Name', data.lastName || ''],
    ['Full Name', data.fullName || `${data.firstName || ''} ${data.lastName || ''}`.trim()],
    ['Gender', data.gender || ''],
    ['Email Address', data.studentEmail || ''],
    ['Phone Number', data.studentPhone || ''],
    ['Program of Interest', data.programInterest || ''],
    ['Tell Us More About Your Requirements', data.programOtherDetails || ''],
    ['How Did You Hear About Us', data.hearAbout || ''],
    ['Please Specify', data.hearOtherSpecify || ''],
    ['Additional Notes', data.otherRequirements || '']
  ];

  rows.forEach(([label, value]) => {
    body.appendParagraph(`${label}: ${value || ''}`);
  });

  body.appendParagraph('');
  body.appendParagraph('Uploaded File Links:');

  if (Object.keys(fileLinks).length) {
    Object.keys(fileLinks).forEach(label => {
      body.appendParagraph(`${label}: ${fileLinks[label]}`);
    });
  } else {
    body.appendParagraph('No uploaded documents.');
  }

  doc.saveAndClose();

  const docFile = DriveApp.getFileById(doc.getId());
  const pdfBlob = docFile.getBlob().setName(`${inquiryId} - Inquiry Summary.pdf`);
  const pdfFile = submissionFolder.createFile(pdfBlob);

  docFile.setTrashed(true);

  return pdfFile.getUrl();
}

function jsonResponse(obj) {
  return ContentService
    .createTextOutput(JSON.stringify(obj))
    .setMimeType(ContentService.MimeType.JSON);
}
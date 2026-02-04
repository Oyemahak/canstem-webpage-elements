// Code.gs

/** Serve the web app UI (embeddable) */
function doGet() {
  return HtmlService.createHtmlOutputFromFile('index')
    .setTitle('CanSTEM Course Requests')
    .setXFrameOptionsMode(HtmlService.XFrameOptionsMode.ALLOWALL); // allow embed in your site
}

/** Helper: base64 -> Blob (for file attachments) */
function base64ToBlob_(b64Data, contentType, fileName) {
  var bytes = Utilities.base64Decode(b64Data || '');
  return Utilities.newBlob(
    bytes,
    contentType || 'application/octet-stream',
    fileName || 'attachment'
  );
}

/**
 * Main endpoint called from the web page.
 * Sends the submission to the front desk with any attachments.
 */
function sendToFrontDesk(payload) {
  var GROUP = 'frontdesk@canstemeducation.com';

  var subjectPrefix = payload && payload.type ? payload.type : 'Course Request';
  var subject = subjectPrefix + (payload && payload.name ? (' - ' + payload.name) : '');

  var lines = [];
  lines.push('A new request has been submitted:');
  lines.push('');
  lines.push('Request Type: ' + (payload.type || ''));
  lines.push('Student Full Name: ' + (payload.name || ''));
  lines.push('Student Email: ' + (payload.email || ''));
  lines.push('Phone Number: ' + (payload.phone || ''));

  if (payload.grade)      lines.push('Selected Grade: ' + payload.grade);
  if (payload.courseCode) lines.push('Current Course Code: ' + payload.courseCode);

  if (payload.type === 'Change Course') {
    // In your front-end, newcourse is sent as "CODE - Name"
    lines.push('New Requested Course: ' + (payload.newcourse || ''));
  }

  if (payload.type === 'Mode Switch') {
    // ⭐ Include the new Current Mode field
    lines.push('Current Mode: ' + (payload.currentMode || ''));
    lines.push('Requested Mode (Online or In-Person): ' + (payload.mode || ''));
  }

  if (payload.reason) {
    lines.push('');
    lines.push('Reason: ' + payload.reason);
  }

  lines.push('');
  if (payload.isAdult !== undefined) {
    lines.push('18 or above: ' + (payload.isAdult ? 'Yes' : 'No'));
  }
  if (payload.studentSig) lines.push('Student Signature: ' + payload.studentSig);
  if (payload.parentSig)  lines.push('Parent/Guardian Signature: ' + payload.parentSig);

  lines.push('');
  lines.push('— Automated submission from CanSTEM web form');
  lines.push('Timestamp: ' + Utilities.formatDate(new Date(), Session.getScriptTimeZone() || 'Etc/UTC', 'yyyy-MM-dd HH:mm:ss'));

  var bodyHtml = lines.join('<br>');

  // Build attachments
  var atts = [];
  try {
    if (payload.attachments && payload.attachments.length) {
      payload.attachments.forEach(function (f) {
        if (f && f.dataBase64) {
          atts.push(base64ToBlob_(f.dataBase64, f.mimeType, f.name));
        }
      });
    }
  } catch (e) {
    // Don’t block the email on attachment failure
    Logger.log('Attachment build error: ' + e);
  }

  // Send email
  MailApp.sendEmail({
    to: GROUP,
    subject: subject,
    replyTo: (payload.email || ''),
    name: (payload.name && payload.name.trim()) ? payload.name.trim() : 'CanSTEM',
    htmlBody: bodyHtml,
    attachments: atts
  });

  return { ok: true };
}
// Code.gs
function doGet() {
  return HtmlService.createHtmlOutputFromFile('index')
    .setTitle('CanSTEM Course Requests')
    .setXFrameOptionsMode(HtmlService.XFrameOptionsMode.ALLOWALL); // allow embed
}

// Helper: base64 -> Blob
function base64ToBlob_(b64Data, contentType, fileName) {
  var bytes = Utilities.base64Decode(b64Data);
  return Utilities.newBlob(
    bytes,
    contentType || 'application/octet-stream',
    fileName || 'attachment'
  );
}

// Main: send email to frontdesk with optional attachments
function sendToFrontDesk(payload) {
  var GROUP = 'frontdesk@canstemeducation.com';

  var subjectPrefix = payload.type || 'Course Request';
  var subject = subjectPrefix + (payload.name ? (' – ' + payload.name) : '');

  var lines = [];
  lines.push('A new request has been submitted:');
  lines.push('');
  lines.push('Request Type: ' + (payload.type || ''));
  lines.push('Student Full Name: ' + (payload.name || ''));
  lines.push('Student Email: ' + (payload.email || ''));
  lines.push('Phone Number: ' + (payload.phone || ''));
  lines.push('Current Course Name & Code: ' + (payload.course || ''));
  if (payload.grade)   lines.push('Selected Grade: ' + payload.grade);
  if (payload.subject) lines.push('Selected Subject: ' + payload.subject);
  if (payload.type === 'Change Course') {
    lines.push('New Requested Course: ' + (payload.newcourse || ''));
  }
  if (payload.type === 'Mode Switch') {
    lines.push('Requested Mode (Online or In-Person): ' + (payload.mode || ''));
  }
  if (payload.reason) {
    lines.push('');
    lines.push('Reason: ' + payload.reason);
  }
  lines.push('');
  lines.push('— Automated submission from CanSTEM web form');

  var bodyHtml = lines.join('<br>');

  var atts = [];
  if (payload.attachments && payload.attachments.length) {
    payload.attachments.forEach(function (f) {
      try { atts.push(base64ToBlob_(f.dataBase64, f.mimeType, f.name)); } catch(e) {}
    });
  }

  MailApp.sendEmail({
    to: GROUP,
    subject: subject,
    replyTo: payload.email || '',
    // Show the student name as the sender display name
    name: (payload.name && payload.name.trim()) ? payload.name.trim() : 'CanSTEM',
    htmlBody: bodyHtml,
    noReply: true,
    attachments: atts
  });

  return { ok: true };
}

/*********************************
 * CanSTEM Change Requests - Code.gs
 *********************************/

const FRONTDESK_EMAIL = 'frontdesk@canstemeducation.com';
const CC_STUDENT      = true;
const SCRIPT_TZ       = Session.getScriptTimeZone() || 'America/Toronto';

function doGet() {
  return HtmlService
    .createHtmlOutputFromFile('index')
    .setTitle('CanSTEM Education - Change Requests')
    .setXFrameOptionsMode(HtmlService.XFrameOptionsMode.ALLOWALL);
}

function sendToFrontDesk(payload) {
  try {
    validatePayload_(payload);

    const subject     = makeSubject_(payload);
    const htmlBody    = makeHtmlBody_(payload);
    const attachments = buildAttachments_(payload.attachments || []);

    const options = {
      name: 'Course (Withdrawal / Change / Mode Switch) Request Form',
      htmlBody,
      attachments,
      replyTo: isEmail_(payload.email) ? payload.email.trim() : undefined
    };
    if (CC_STUDENT && payload.email && isEmail_(payload.email)) {
      options.cc = payload.email.trim();
    }
    MailApp.sendEmail(FRONTDESK_EMAIL, subject, stripHtml_(htmlBody), options);
    return { ok: true };
  } catch (err) {
    throw new Error(err && err.message ? err.message : String(err));
  }
}

/* -------------------- Validation & Formatting -------------------- */

function validatePayload_(p) {
  if (!p || typeof p !== 'object') throw new Error('No form data received.');

  const required = ['type', 'name', 'email', 'phone', 'grade', 'courseCode', 'studentSig'];
  required.forEach(k => {
    if (!p[k] || String(p[k]).trim() === '') throw new Error(`Missing required field: ${k}`);
  });

  if (!isEmail_(p.email)) throw new Error('Please provide a valid student email address.');
  if (!p.reason || String(p.reason).trim() === '') {
    throw new Error('Please provide the reason for your request.');
  }

  if (!p.isAdult) {
    if (!p.parentSig || String(p.parentSig).trim() === '') {
      throw new Error('Parent/Guardian signature is required for students under 18.');
    }
  }

  const type = String(p.type || '').toLowerCase();
  if (type === 'change course') {
    if (!p.newcourse || String(p.newcourse).trim() === '') {
      throw new Error('New requested course is required.');
    }
  } else if (type === 'mode switch') {
    if (!p.currentMode || !p.mode) {
      throw new Error('Current mode and requested mode are required.');
    }
  }
}

function requestLabel_(p) {
  const t = String(p.type || '').toLowerCase();
  if (t === 'withdrawal')     return 'Course Withdrawal Request';
  if (t === 'change course')  return 'Course Change Request';
  if (t === 'mode switch')    return 'Course Mode Switch Request';
  return 'Course Request';
}

function makeSubject_(p) {
  const safeName = oneLine_(p.name);
  const safeCode = oneLine_(p.courseCode);
  return `${requestLabel_(p)} - ${safeName} - ${safeCode}`;
}

function makeHtmlBody_(p) {
  const rows = [];
  const sep = '<tr><td colspan="2" style="padding:0"><hr style="border:none;border-top:1px solid #e5e7eb;margin:8px 0"></td></tr>';
  const headerTitle = 'New ' + requestLabel_(p);

  rows.push(row_('Request Type', p.type));
  rows.push(row_('Submitted At', Utilities.formatDate(new Date(), SCRIPT_TZ, 'EEE, MMM d, yyyy • h:mm a')));
  rows.push(sep);

  // Student
  rows.push(row_('Student Name', p.name));
  rows.push(row_('Student Email', p.email));
  rows.push(row_('Phone', p.phone));
  rows.push(row_('18 or above?', p.isAdult ? 'Yes' : 'No'));
  rows.push(sep);

  // Course info
  rows.push(row_('Grade', p.grade));
  rows.push(row_('Current Course Code', p.courseCode));

  const type = String(p.type || '').toLowerCase();
  if (type === 'change course') rows.push(row_('New Requested Course', p.newcourse));
  if (type === 'mode switch') {
    rows.push(row_('Current Mode', p.currentMode));
    rows.push(row_('Requested Mode', p.mode));
  }

  rows.push(sep);
  rows.push(row_('Reason', p.reason));

  // Only show payment acknowledgment for Change/Mode (NOT for Withdrawal)
  if (type !== 'withdrawal') {
    rows.push(row_('Payment Marked as Done', p.paidConfirmed ? 'Yes' : 'No'));
  }

  rows.push(sep);

  // Signatures
  rows.push(row_('Student Signature', p.studentSig));
  if (!p.isAdult) rows.push(row_('Parent/Guardian Signature', p.parentSig || '(missing)'));

  // Attachment summary
  const attachNotes = [];
  if (p.hasPayment)     attachNotes.push('Proof of Payment attached');
  if (p.hasPrereq)      attachNotes.push('Proof of Prerequisite attached');
  if (p.hasSupporting)  attachNotes.push('Supporting document(s) attached');
  const attachmentsNote = attachNotes.length ? attachNotes.join(' • ') : 'No files attached.';

  rows.push(sep);
  rows.push(row_('Files', attachmentsNote));

  const styles = `
    <style>
      body{font-family:Arial,Helvetica,sans-serif;color:#0f172a}
      .wrap{max-width:720px;margin:0 auto;padding:16px}
      h2{margin:0 0 12px 0;color:#001161}
      table{border-collapse:separate;border-spacing:0;width:100%;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden}
      th,td{padding:10px 12px;vertical-align:top;font-size:14px}
      th{background:#f8fafc;text-align:left;width:240px;color:#0b1324}
      tr+tr td,tr+tr th{border-top:1px solid #e5e7eb}
      .small{margin-top:10px;color:#475569;font-size:12px}
    </style>
  `;

  return `${styles}
    <div class="wrap">
      <h2>${esc_(headerTitle)}</h2>
      <table>${rows.join('')}</table>
      <p class="small">This message was sent automatically from the CanSTEM Change Request form.</p>
    </div>`;
}

function row_(label, value) {
  return `<tr><th>${esc_(label)}</th><td>${nl2br_(esc_(String(value ?? '')))}</td></tr>`;
}

function buildAttachments_(files) {
  if (!Array.isArray(files)) return [];
  const out = [];
  files.forEach((f, idx) => {
    try {
      if (!f || !f.dataBase64) return;
      const bytes = Utilities.base64Decode(f.dataBase64);
      const name = (f.name && String(f.name).trim()) || `attachment-${idx + 1}`;
      const mime = (f.mimeType && String(f.mimeType).trim()) || 'application/octet-stream';
      out.push(Utilities.newBlob(bytes, mime, name));
    } catch (e) {
      Logger.log('Attachment error: ' + e);
    }
  });
  return out;
}

/* -------------------- Utils -------------------- */
function esc_(s){return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');}
function nl2br_(s){return String(s).replace(/\r?\n/g,'<br>');}
function oneLine_(s){return String(s).replace(/\s+/g,' ').trim();}
function stripHtml_(html){return oneLine_(html.replace(/<[^>]*>/g,' '));}
function isEmail_(s){return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(s||'').trim());}
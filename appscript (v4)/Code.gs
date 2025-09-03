/*********************************
 * CanSTEM Course Requests – Code.gs
 * Works with Index.html you provided
 *********************************/

const FRONTDESK_EMAIL = 'frontdesk@canstemeducation.com';
const CC_STUDENT = true; // set to false if you don't want to CC the student

/** Serve the HTML */
function doGet() {
  return HtmlService
    .createHtmlOutputFromFile('Index') // <-- your HTML file must be named "Index.html"
    .setTitle('CanSTEM Education – Course Requests');
}

/** Main entrypoint called from client */
function sendToFrontDesk(payload) {
  try {
    validatePayload_(payload);

    const subject = makeSubject_(payload);
    const htmlBody = makeHtmlBody_(payload);
    const attachments = buildAttachments_(payload.attachments || []);

    const options = {
      name: 'CanSTEM Request Bot',
      htmlBody,
      attachments
    };

    // Optionally CC the student
    if (CC_STUDENT && payload.email && isEmail_(payload.email)) {
      options.cc = payload.email.trim();
    }

    MailApp.sendEmail(FRONTDESK_EMAIL, subject, stripHtml_(htmlBody), options);
    return { ok: true };
  } catch (err) {
    // Make sure a useful error reaches the client failure handler
    throw new Error(err && err.message ? err.message : String(err));
  }
}

/* -------------------- Helpers -------------------- */

function validatePayload_(p) {
  if (!p || typeof p !== 'object') throw new Error('No form data received.');

  const required = ['type', 'name', 'email', 'phone', 'grade', 'courseCode', 'studentSig'];
  required.forEach(k => {
    if (!p[k] || String(p[k]).trim() === '') {
      throw new Error(`Missing required field: ${k}`);
    }
  });

  if (!isEmail_(p.email)) {
    throw new Error('Please provide a valid student email address.');
  }

  // Reason is required on all three forms per your UI
  if (!p.reason || String(p.reason).trim() === '') {
    throw new Error('Please provide the reason for your request.');
  }

  // If minor, require parent signature
  const isAdult = !!p.isAdult;
  if (!isAdult) {
    if (!p.parentSig || String(p.parentSig).trim() === '') {
      throw new Error('Parent/Guardian signature is required for students under 18.');
    }
  }

  // Type-specific checks
  const type = String(p.type || '').toLowerCase();
  if (type === 'change course') {
    if (!p.newcourse) throw new Error('New requested course is required.');
  } else if (type === 'mode switch') {
    if (!p.currentMode || !p.mode) {
      throw new Error('Current mode and requested mode are required.');
    }
  }
}

function makeSubject_(p) {
  const safeName = oneLine_(p.name);
  const safeCode = oneLine_(p.courseCode);
  const t = String(p.type || '').trim();
  return `[CanSTEM Request] ${t} – ${safeName} – ${safeCode}`;
}

function makeHtmlBody_(p) {
  const rows = [];
  rows.push(row_('Request Type', p.type));
  rows.push(row_('Submitted At', Utilities.formatDate(new Date(), Session.getScriptTimeZone() || 'America/Toronto', 'EEE, MMM d, yyyy • h:mm a')));

  // Student info
  rows.push(row_('Student Name', p.name));
  rows.push(row_('Student Email', p.email));
  rows.push(row_('Phone', p.phone));
  rows.push(row_('18 or above?', p.isAdult ? 'Yes' : 'No'));

  // Course info
  rows.push(row_('Grade', p.grade));
  rows.push(row_('Current Course Code', p.courseCode));

  const type = String(p.type || '').toLowerCase();
  if (type === 'change course') {
    rows.push(row_('New Requested Course', p.newcourse));
  }
  if (type === 'mode switch') {
    rows.push(row_('Current Mode', p.currentMode));
    rows.push(row_('Requested Mode', p.mode));
  }

  // Reason
  rows.push(row_('Reason', p.reason));

  // Signatures
  rows.push(row_('Student Signature', p.studentSig));
  if (!p.isAdult) {
    rows.push(row_('Parent/Guardian Signature', p.parentSig || '(missing)'));
  }

  const attachmentsNote = (p.attachments && p.attachments.length) ? `${p.attachments.length} attachment(s) included.` : 'No attachments.';
  rows.push(row_('Attachments', attachmentsNote));

  const styles = `
    <style>
      body{font-family:Arial,Helvetica,sans-serif;color:#0f172a}
      .wrap{max-width:720px;margin:0 auto;padding:16px}
      h2{margin:0 0 12px 0;color:#001161}
      table{border-collapse:separate;border-spacing:0;width:100%;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden}
      th,td{padding:10px 12px;vertical-align:top;font-size:14px}
      th{background:#f8fafc;text-align:left;width:220px;color:#0b1324}
      tr+tr td,tr+tr th{border-top:1px solid #e5e7eb}
      .small{margin-top:10px;color:#475569;font-size:12px}
    </style>
  `;

  return `
    ${styles}
    <div class="wrap">
      <h2>New Course Request Submission</h2>
      <table>${rows.join('')}</table>
      <p class="small">This message was sent automatically from the CanSTEM Course Request form.</p>
    </div>
  `;
}

function row_(label, value) {
  return `<tr><th>${esc_(label)}</th><td>${nl2br_(esc_(String(value ?? '')))}</td></tr>`;
}

/** Build Gmail attachments from base64 array coming from the client. */
function buildAttachments_(files) {
  if (!Array.isArray(files)) return [];
  const out = [];
  files.forEach((f, idx) => {
    try {
      if (!f || !f.dataBase64) return;
      const bytes = Utilities.base64Decode(f.dataBase64);
      const name = (f.name && String(f.name).trim()) || `attachment-${idx+1}`;
      const mime = (f.mimeType && String(f.mimeType).trim()) || 'application/octet-stream';
      out.push(Utilities.newBlob(bytes, mime, name));
    } catch (e) {
      // Skip bad file but continue sending email
      Logger.log('Attachment error: ' + e);
    }
  });
  return out;
}

/* ---------- small utils ---------- */

function esc_(s) {
  return String(s)
    .replace(/&/g,'&amp;')
    .replace(/</g,'&lt;')
    .replace(/>/g,'&gt;')
    .replace(/"/g,'&quot;')
    .replace(/'/g,'&#39;');
}

function nl2br_(s) {
  return String(s).replace(/\r?\n/g, '<br>');
}

function oneLine_(s) {
  return String(s).replace(/\s+/g, ' ').trim();
}

function stripHtml_(html) {
  // Plain text fallback body required by MailApp
  return oneLine_(html.replace(/<[^>]*>/g, ' '));
}

function isEmail_(s) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(s || '').trim());
}
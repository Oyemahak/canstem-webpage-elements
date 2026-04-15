# CanSTEM Webpage Elements

This repository is the working code library for CanSTEM Education website pages, reusable webpage blocks, forms, payment snippets, SEO snippets, LMS documentation, and course outline content.

It started as a small collection of HTML components, but it now acts as a central workspace for many parts of the CanSTEM website. Most files are standalone snippets or page sections that are copied into WordPress pages, WordPress theme/plugin snippets, Google Apps Script projects, or LMS-related workflows.

## What This Repo Is For

- Building and storing CanSTEM website page code.
- Keeping reusable HTML/CSS/JavaScript sections organized.
- Managing frontend and backend code for website forms.
- Keeping Google Apps Script form handlers and older script versions.
- Storing payment page frontend and WordPress REST API backend snippets.
- Keeping course outline tables, units, templates, and experiments.
- Saving SEO, Google Search Console, robots, pixel, and redirect/HTTPS snippets.
- Documenting LMS changes, Moodle cleanup work, screenshots, and instructions.

This is not a single packaged web app with one build command. It is a code and snippet repository for website operations and page development.

## Repository Snapshot

At the time this README was updated, the repo contains about 249 working files, including:

- HTML page sections and full page snippets.
- PHP snippets for WordPress pages, REST endpoints, 404 pages, and SEO/search-console adjustments.
- JavaScript and Google Apps Script files for form handling.
- CSS snippets and templates.
- Text notes, old configuration snippets, documentation files, screenshots, and LMS records.

## Main Folder Guide

### `Pages/`

Contains most website page-specific code. These are usually page sections, complete page layouts, dev/prod versions, CTAs, accordions, FAQ blocks, pricing sections, blog content, and WordPress-ready snippets.

Current page areas include:

- `404/` - custom 404 page versions.
- `AP Courses/` - AP course page versions.
- `Adult Education/` - adult education content and FAQ blocks.
- `CAEC-GED/` - CAEC, GED, mature student, LOA, and after-school-care content.
- `CELPIP/`, `IELTS/`, `PTE/` - language test pages, fee blocks, FAQ blocks, and production/dev versions.
- `Computer Training Courses/` - computer skills program page code.
- `Course Withdrawal-Change-Mode Policy/` - CTA content for policy pages.
- `Fees Structure Page/` - current, dev, prod, archived, dropdown, and search bar versions for fee structure pages.
- `Final Exam/` - final exam CTA blocks.
- `Full Time Page/` - full-time school accordions, FAQ sections, international/full-time content.
- `Header/` - WordPress menu/header shortcode snippets for dev and production.
- `High school Credits/` - high school credit fee content.
- `Home/` - homepage notices and search bar versions.
- `ILO/` - independent learning / online learning page versions.
- `International Students/` - international student fee and FAQ sections.
- `Kangaroo Math Cotest/` - Kangaroo Math Contest page code.
- `OSSD Requirements 2025/` - tables, accordions, heading buttons, and category sections for OSSD requirements.
- `OUAC/` - how-to video content and table of contents.
- `Our Blogs/` - blog page HTML blocks.
- `Payment Page/` and `Secure Online Payment/` - payment page frontend and backend snippets.
- `Summer School/` - summer school hero and body blocks.
- `Tutoring Page/` - new and old accordion versions plus notes.
- `University Open House/` - coming soon block.

### `FORMS/`

Contains frontend and backend code for CanSTEM forms.

Important areas:

- `Enrollment Forms/High School/`
- `Enrollment Forms/Inquiry/`
- `Enrollment Forms/Tutoring/`
- `Course Change Form/`
- `Final Exam/`
- `App Script/`

Many enrollment form folders are organized into:

- `html/` - frontend form markup and browser-side JavaScript.
- `php/` - WordPress/PHP backend handlers or snippets.
- `appscript/` - Google Apps Script code for submissions, Google Sheets, Drive folders, PDFs, or email handling.
- `dev.*` and `prod.*` files - development and production versions.

`FORMS/App Script/appscript (v6)/` contains the current Apps Script course request form code. `appscript OLD Versions/` keeps historical versions, including versions marked as dead, for reference.

### `Course Outlines/`

Contains reusable course outline templates, individual course outline blocks, and experiments.

- `Template/` - template HTML, CSS, and JS for course outlines.
- `All Courses/` - course-specific outline blocks, usually split into table and unit files.
- `Tryouts/` - experimental course outline and course change layouts.

Current course folders include examples such as `bat4m`, `eae2d`, `eng2d`, `hrt3m`, `mhf4u`, `nbe3u`, `olc4o`, `sph4u`, `tas1o`, and `tas2o`.

### `Single Elements/`

Reusable blocks and snippets that are not tied to one full page.

Examples include:

- Footer/sub-footer code.
- Coming soon blocks.
- Notices.
- Course lists.
- Compact all-course displays.
- LMS email links.
- Product archive snippets.
- GTranslate CSS.
- Password/live link notes.

### `Language Programs/`

Older or standalone language program components and blocks, including PTE, CELPIP, and CAEC-related snippets.

Examples:

- `PTEPriceBanner.html`
- `PTETestSelector.html`
- `CelpipInfo.html`
- `celpip.html`
- `caec.html`

### `Breadcrubs/`

Contains breadcrumb snippets for different website pages. The folder name is currently spelled `Breadcrubs` in the repo, so keep that spelling when linking paths or searching.

Examples include breadcrumbs for:

- OUAC
- OSSD
- ILO
- Fee structure
- Secure online payment
- Final exam request
- Course change and course mode policy
- Live links
- Student services

### `LMS/`

Contains LMS and Moodle-related files, instructions, screenshots, cleanup records, and documentation.

Examples:

- Moodle upgrade documentation.
- LMS important instructions.
- Disable paste snippets.
- Ministry guideline blocks.
- MHF4U LMS content.
- Screenshots and records from LMS cleanup, course reset, student deletion, and production changes.

### `SEO/`

Contains SEO and technical website snippets.

Examples:

- Facebook/Meta pixel snippet.
- Yoast settings and schema snippets.
- Robots.txt versions.
- HTTPS/URL-related code.
- Old and new `.htaccess` snippets.

### `Google Search Console/`

Contains WordPress/PHP code related to search console or protected page indexing/schema handling.

### `Arrows/`

Small reusable arrow animation snippets.

### `Product Categories Playground/`

Experimental product/category page code and planning notes.

### `WP-Rocket/`

Screenshots and notes related to WP Rocket/performance configuration.

## Common File Naming Conventions

Several folders use naming conventions that matter:

- `dev` - development or draft version.
- `prod` - production-ready or live-site version.
- `try` - experiment or test version.
- `old`, `previous`, `archive`, `Archieve Pages` - older saved versions kept for reference.
- `CTA` - call-to-action section.
- `table`, `unit`, `accordion`, `faq`, `fees` - content type or page section.

Before updating the live website, compare the matching `dev` and `prod` files and confirm which one is currently being used.

## How To Use This Repo

Clone the repository:

```bash
git clone https://github.com/Oyemahak/canstem-webpage-elements.git
cd canstem-webpage-elements
```

Open the folder in your editor and search by page, form, course code, or feature name.

Useful search examples:

```bash
rg "PTE"
rg "High School" FORMS
rg "MHF4U" "Course Outlines"
rg "canstem_process_payment"
```

Most HTML files can be opened directly in a browser for visual checking, but files that depend on WordPress shortcodes, PHP, REST routes, Google Apps Script, or live website assets must be tested in the correct environment.

## Working With Pages

1. Find the matching folder inside `Pages/`.
2. Check whether there is a `dev`, `prod`, `try`, or archived version.
3. Make edits in the correct working file.
4. Test the HTML visually when possible.
5. Copy the final snippet into the correct WordPress page, widget, shortcode, or theme/plugin location.
6. Save the updated production snippet back into this repo.

## Working With Forms

Form work may involve multiple layers:

- HTML frontend in `FORMS/.../html/`.
- PHP backend in `FORMS/.../php/`.
- Google Apps Script in `FORMS/.../appscript/` or `FORMS/App Script/`.
- External services such as Google Sheets, Google Drive, email, payment systems, or WordPress AJAX/REST endpoints.

When editing forms:

- Keep `dev` and `prod` versions separate.
- Confirm endpoint URLs before moving code to production.
- Test required fields, conditional fields, file uploads, payment verification, and success/error states.
- Check that submissions reach the correct email inbox, Google Sheet, Google Drive folder, or backend handler.
- Do not remove old Apps Script versions unless they are confirmed unnecessary.

## Working With Secure Online Payment

Payment code is stored under:

```text
Pages/Secure Online Payment/
```

The folder includes:

- `Frontend/` - payment page HTML/frontend code.
- `Backend/` - WordPress/PHP REST API payment snippets.

Important: payment code can contain production credentials or live endpoint logic. Treat it carefully. Credentials should ideally be moved into environment variables, WordPress configuration, or another secure secret store rather than kept directly in snippets.

## Working With Course Outlines

Course outline code lives in:

```text
Course Outlines/
```

Use `Course Outlines/Template/` as the starting point for new course outline layouts. Existing course folders usually split the outline into:

- `*-table.html` - course summary and unit table.
- `*-unit.html` - detailed unit/expectation accordion or content block.

Keep course codes lowercase in folder names unless there is a reason to match an existing pattern.

## Production Safety Checklist

Before copying code to the live website:

- Confirm whether the source file is `dev`, `prod`, `try`, or archived.
- Review links, phone numbers, email addresses, fees, dates, and course names.
- Check mobile layout and desktop layout.
- Check that forms submit successfully.
- Check that payment snippets use the correct environment.
- Remove console logs or temporary debugging text.
- Avoid exposing private keys, access tokens, sheet IDs, or webhook URLs in public-facing code.
- Save the final version back into this repo so the repo stays aligned with the live site.

## Suggested Housekeeping

The repo currently includes some local/system files such as `.DS_Store` in a few folders. Future cleanup can remove those files and add common local artifacts to `.gitignore`.

Good candidates for ongoing cleanup:

- Remove duplicate or unused experiments once the final version is chosen.
- Move sensitive credentials out of committed snippets.
- Keep archive folders clearly labelled.
- Add short notes inside complex form folders explaining which version is live.
- Keep `dev` and `prod` files updated together when production changes are made.

## Who This Repo Helps

This repository is useful for:

- Website developers updating CanSTEM pages.
- Staff or contractors checking what code exists for a page or form.
- Anyone reviewing how CanSTEM website forms connect frontend, backend, Apps Script, email, Drive, Sheets, and payment workflows.
- Future maintainers who need to understand where page sections, form code, course outlines, SEO snippets, and LMS-related assets are stored.
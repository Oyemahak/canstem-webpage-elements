<?php
if ( ! defined('ABSPATH') ) { exit; }
get_header();
echo do_shortcode("[hfe_template id='1642']");
?>
<style>
  /* Hide theme header/footer (we use HFE shortcodes instead) */
  body.error404 .site-header,
  body.error404 header#masthead,
  body.error404 .site-footer,
  body.error404 footer#colophon { display: none !important; }

  body.error404 .ast-container,
  body.error404 .site-content .ast-container,
  body.error404 .ast-container-fluid {
    max-width: 100% !important;
    margin: 0 auto !important;
    padding-left: 0 !important;
    padding-right: 0 !important;
    box-sizing: border-box;
  }
  @media (min-width: 922px){
    body.error404 .site-content .ast-container{ display:inline-block !important; width:100%; }
  }

  /* ===== Stack order: header (top) > search > content card > bg/separator ===== */
  /* Bring your HFE header (id 1642) and any Elementor header wrappers above everything */
  body.error404 .elementor-1642,
  body.error404 [data-elementor-id="1642"],
  body.error404 .elementor-location-header,
  body.error404 [data-elementor-type="header"],
  body.error404 header.hfe-header,
  body.error404 .hfe-sticky,
  body.error404 .hfe-sticky-header {
    position: relative !important;
    z-index: 2147483800 !important; /* safely above page content */
  }

  #canstem-404{
    --head:#001161; --sub:#00427c; --ink:#26262b; --border:#e2e8f0; --deep:#001161;
    font-family:"Open Sans",system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;
    color:var(--ink); text-align:center; padding:28px 16px 24px;
    position:relative; overflow:visible;
    background:
      radial-gradient(1400px 560px at 50% -240px,#eaf2ff 0%,transparent 70%),
      linear-gradient(180deg,#ffffff 0%,#ffffff 100%);
    z-index: 1; /* below header, above page bg */
  }

  .book-spray svg{
    position:absolute; width:110px; height:auto; opacity:.07; transform:rotate(8deg);
    filter:drop-shadow(0 4px 10px rgba(0,0,0,.06)); pointer-events:none; z-index:0;
  }
  .book-spray .b1{ top:6%; left:6%; animation:floatA 7s ease-in-out infinite; }
  .book-spray .b2{ top:14%; right:8%; transform:rotate(-6deg); animation:floatB 6.5s ease-in-out infinite; }
  .book-spray .b3{ bottom:18%; left:10%; transform:rotate(14deg); animation:floatC 7.5s ease-in-out infinite; }
  .book-spray .b4{ bottom:10%; right:12%; transform:rotate(-12deg); animation:floatA 6.8s ease-in-out infinite; }
  @keyframes floatA{0%,100%{transform:translateY(0) rotate(8deg);}50%{transform:translateY(-6px) rotate(8deg);}}
  @keyframes floatB{0%,100%{transform:translateY(0) rotate(-6deg);}50%{transform:translateY(-7px) rotate(-6deg);}}
  @keyframes floatC{0%,100%{transform:translateY(0) rotate(14deg);}50%{transform:translateY(-5px) rotate(14deg);}}
  @media (prefers-reduced-motion:reduce){ .book-spray svg{ animation:none; } }

  #canstem-404 .wrap{
    width:100%; max-width:760px; margin:16px auto 20px; background:#fff;
    border:1px solid var(--border); border-radius:16px; padding:26px 20px;
    box-shadow:0 14px 40px rgba(2,6,23,.10);
    position:relative; z-index:100; /* content card, below search+header */
  }

  #canstem-404 h1{ margin:0 0 8px; color:var(--head); font-weight:900; font-size:clamp(34px,5.2vw,48px); }
  #canstem-404 p{ margin:0 14px 18px; font-size:clamp(15px,2vw,18px); color:#4a4f57; }

  #canstem-404 .actions{ display:flex; flex-wrap:wrap; justify-content:center; gap:10px; margin-top:14px; }
  #canstem-404 .btn{
    display:inline-flex; align-items:center; justify-content:center; gap:8px;
    padding:12px 18px; border-radius:10px; text-decoration:none; font-weight:800; letter-spacing:.01em;
    font-size:15px; line-height:1; box-shadow:0 2px 10px rgba(0,0,0,.06);
    transition:transform .06s ease, filter .2s ease, background-color .2s ease, color .2s ease, border-color .2s ease;
  }
  #canstem-404 .btn:active{ transform:translateY(1px); }
  #canstem-404 .btn-primary{ background:linear-gradient(180deg,#0d4c8f,#0a3c73); color:#fff; border:1px solid #08325e; }
  #canstem-404 .btn-primary:hover{ filter:brightness(1.06); }
  #canstem-404 .btn-ghost{ background:#fff; color:#0a0a0a; border:1px solid var(--border); }
  #canstem-404 .btn-ghost:hover{ background:#f7fbff; border-color:#c7d2fe; }

  /* Footer separator behind everything interactive */
  .footer-separator{
    position:relative; height:40px; background:linear-gradient(180deg,#eaf2ff 0%,#ffffff 90%);
    margin-top:6px; z-index:0 !important; pointer-events:none;
  }
  .footer-separator::before{
    content:""; position:absolute; left:50%; transform:translateX(-50%); top:-20px;
    width:68px; height:42px; background:#eaf3ff; border-top-left-radius:80px 60px; border-top-right-radius:80px 60px;
    box-shadow:0 -6px 0 var(--deep); z-index:0 !important;
  }
  .footer-separator::after{
    content:""; position:absolute; left:0; right:0; top:18px; height:4px; background:var(--deep); z-index:0 !important;
  }

  @media (max-width:767.98px){
    #canstem-404 .wrap{ max-width:560px; padding:22px 16px; }
    #canstem-404 .actions .btn{ min-width:170px; }
  }

  /* Search box always above bg/separator; suggestions above all content */
  body.error404 #canstem-course-finder{ position:relative; z-index:2147483500; }
  body.error404 #canstem-course-finder .csbx-suggest{ z-index:2147483600 !important; }
</style>

<?php if ( astra_page_layout() === 'left-sidebar' ) { get_sidebar(); } ?>

<div id="primary" <?php astra_primary_class(); ?>>
  <?php astra_primary_content_top(); ?>

  <section id="canstem-404" role="main" aria-labelledby="canstem-404-title">
    <div class="book-spray" aria-hidden="true">
      <svg class="b1" viewBox="0 0 128 128"><path d="M20 20h60a10 10 0 0 1 10 10v68a6 6 0 0 1-6 6H24a8 8 0 0 1-8-8V26a6 6 0 0 1 6-6z" fill="#00427c"/><path d="M28 28h60a10 10 0 0 1 10 10v64" fill="none" stroke="#001161" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/></svg>
      <svg class="b2" viewBox="0 0 128 128"><path d="M20 20h60a10 10 0 0 1 10 10v68a6 6 0 0 1-6 6H24a8 8 0 0 1-8-8V26a6 6 0 0 1 6-6z" fill="#001161"/><path d="M24 38h52M24 54h52M24 70h40" stroke="#fff" stroke-width="6" stroke-linecap="round" opacity=".8"/></svg>
      <svg class="b3" viewBox="0 0 128 128"><path d="M20 20h60a10 10 0 0 1 10 10v68a6 6 0 0 1-6 6H24a8 8 0 0 1-8-8V26a6 6 0 0 1 6-6z" fill="#00427c"/><path d="M28 28h60a10 10 0 0 1 10 10v64" fill="none" stroke="#001161" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/></svg>
      <svg class="b4" viewBox="0 0 128 128"><path d="M20 20h60a10 10 0 0 1 10 10v68a6 6 0 0 1-6 6H24a8 8 0 0 1-8-8V26a6 6 0 0 1 6-6z" fill="#001161"/><path d="M24 38h52M24 54h52M24 70h40" stroke="#fff" stroke-width="6" stroke-linecap="round" opacity=".8"/></svg>
    </div>

    <div class="wrap">
      <h1 id="canstem-404-title">404</h1>
      <p>The page you requested does not exist</p>

      <!-- ============== CanSTEM Search (Courses + Pages + Blogs) ============== -->
      <section id="canstem-course-finder" aria-label="Find courses, pages, or blogs">
        <div class="csbx-shell" role="search">
          <svg class="csbx-search" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <circle cx="11" cy="11" r="6.5" fill="none" stroke="currentColor" stroke-width="2" />
            <path d="M20 20L16.2 16.2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
          </svg>
          <input id="csbx-q" class="csbx-input" type="search" spellcheck="false" autocomplete="off"
                 placeholder="Search here.." aria-autocomplete="list" aria-controls="csbx-suggest" aria-expanded="false" />
          <div id="csbx-suggest" class="csbx-suggest" role="listbox" aria-label="Suggestions"></div>
        </div>
      </section>

      <style>
        #canstem-course-finder{
          --csbx-ink:#0f172a; --csbx-muted:#667085; --csbx-bg:#fff; --csbx-border:#e6e6e6;
          --csbx-ring:#fbb02547; --csbx-ring-soft:rgba(251,176,37,.18); --csbx-ring-strong:rgba(251,176,37,.36);
          --csbx-brand:#001161; --csbx-soft:#f8fafc; --csbx-shadow:0 10px 30px rgba(2,6,23,.10);
          --csbx-radius:999px; --csbx-h:52px; --csbx-font:18px; --csbx-menu-radius:14px;
          max-width:1200px; margin:0 auto; padding:0;
        }
        #canstem-course-finder input,
        #canstem-course-finder button,
        #canstem-course-finder ul,
        #canstem-course-finder li{ all:unset; }
        #canstem-course-finder input,
        #canstem-course-finder button{ box-shadow:none !important; background:none !important; }

        #canstem-course-finder .csbx-shell{
          position:relative; display:flex; align-items:center; height:var(--csbx-h);
          background:var(--csbx-bg); border:1px solid var(--csbx-brand); border-radius:var(--csbx-radius);
          padding:0 16px 0 44px; box-shadow:0 4px 28px rgba(251,176,37,.6), 0 0 0 0 var(--csbx-ring);
          transition: box-shadow .18s ease, border-color .18s ease;
        }
        #canstem-course-finder .csbx-shell:focus-within{ border:1px solid #000; outline:.5px solid #000; }

        #canstem-course-finder .csbx-search{
          position:absolute; left:16px; top:50%; transform:translateY(-50%);
          width:20px; height:20px; color:#9aa5b1; pointer-events:none;
        }
        #canstem-course-finder .csbx-input{
          flex:1 1 auto; text-align:left; min-width:0; height:2.5rem; line-height:normal; padding:0;
          font-size:var(--csbx-font); color:var(--csbx-ink) !important; caret-color:var(--csbx-ink);
        }
        #canstem-course-finder .csbx-input::placeholder{ color:#9aa5b1; opacity:1; }

        #canstem-course-finder .csbx-suggest{
          position:absolute; left:0; right:0; top:calc(100% + 10px);
          background:#fff; border:1px solid var(--csbx-border); border-radius:18px;
          box-shadow:var(--csbx-shadow); padding:8px; max-height:420px; overflow:auto;
          z-index:8; display:none;
        }
        #canstem-course-finder .csbx-suggest.csbx-show{ display:block; }

        #canstem-course-finder .csbx-section{ margin:4px 0 10px 0; }
        #canstem-course-finder .csbx-section-title{
          font-size:12px; font-weight:600; letter-spacing:.06em; text-transform:uppercase; color:#475569;
          padding:8px 12px; margin:6px; background:#f8fafc; border:1px solid #eef2f7; border-radius:8px;
        }
        #canstem-course-finder .csbx-row{
          display:grid; grid-template-columns:1fr auto; gap:10px 14px; align-items:center;
          padding:12px 14px; border-radius:12px; cursor:pointer; transition:background .12s ease; border-bottom:1px solid #f1f5f9;
        }
        #canstem-course-finder .csbx-row:hover,
        #canstem-course-finder .csbx-row[aria-selected="true"]{ background:#f7fafc; }
        #canstem-course-finder .csbx-row:last-child{ border-bottom:none; }

        #canstem-course-finder .csbx-left{ display:flex; text-align:left; flex-direction:column; min-width:0; }
        #canstem-course-finder .csbx-code{ font-size:19px; font-weight:700; color:var(--csbx-ink); word-break:break-word; }
        #canstem-course-finder .csbx-name{ font-size:15px; font-weight:500; color:#475569; margin-top:2px; line-height:1.35; }
        #canstem-course-finder .csbx-name:empty{ display:none; }
        #canstem-course-finder .csbx-right{ display:flex; flex-wrap:wrap; gap:6px; align-items:center; justify-self:end; }
        #canstem-course-finder .csbx-chip{ font-size:12px; padding:4px 10px; border-radius:999px; white-space:nowrap; border:1px solid transparent; }
        #canstem-course-finder .csbx-chip.course{ background:#ecfdf5; border-color:#a7f3d0; color:#065f46 !important; }
        #canstem-course-finder .csbx-chip.page{ background:#eef2ff; border-color:#e0e7ff; color:#1e3a8a; }
        #canstem-course-finder .csbx-chip.blog{ background:#ecfeff; border-color:#a5f3fc; color:#0e7490; }
        #canstem-course-finder .csbx-section .csbx-row:last-child{ border-bottom:none; }

        @media (max-width:640px){
          #canstem-course-finder .csbx-shell{ padding-right:12px; }
          #canstem-course-finder .csbx-code{ font-weight:600; font-size:14px; }
          #canstem-course-finder .csbx-name{ font-size:14px; }
          #canstem-course-finder .csbx-chip.is-popular{ display:none; }
        }
      </style>
      <!-- ============== /CanSTEM Search ============== -->

      <div class="actions">
        <a class="btn btn-primary" href="<?php echo esc_url( home_url('/') ); ?>">BACK TO HOME</a>
        <a class="btn btn-ghost" href="https://registration.ca.powerschool.com/family/gosnap.aspx?action=100000869&amp;culture=en" target="_blank" rel="noopener">ANY QUESTIONS?</a>
        <a class="btn btn-ghost" href="https://registration.ca.powerschool.com/family/gosnap.aspx?action=100000879&amp;culture=en" target="_blank" rel="noopener">ENROLL NOW!</a>
      </div>
    </div>
  </section>

  <div class="footer-separator" aria-hidden="true"></div>
  <?php astra_primary_content_bottom(); ?>
</div>

<script>
  (function () {
    const D9=[{code:"AVI1O",name:"Visual Arts"},{code:"ADA1O",name:"Drama"},{code:"CGC1W",name:"Issues in Canadian Geography (De-Streamed)"},{code:"ENL1W",name:"English (De-Streamed)"},{code:"FSF1D",name:"French (De-Streamed)"},{code:"GLS1O",name:"Learning Strategies 1: Skills for Success"},{code:"HFN1O",name:"Food and Nutrition"},{code:"HIF1O",name:"Exploring Family Studies"},{code:"PPL1O",name:"Healthy Active Living Education"},{code:"MTH1W",name:"Mathematics (De-Streamed)"},{code:"SNC1W",name:"Science (De-Streamed)"},{code:"TIJ1O",name:"Exploring Technologies"},{code:"TAS1O",name:"Technology and the Skilled Trades"}];

    const D10=[{code:"AVI2O",name:"Visual Arts (G10)"},{code:"ADA2O",name:"Drama (G10)"},{code:"ASM2O",name:"Media Arts (G10)"},{code:"BEP2O",name:"Launching and Leading a Business (G10)"},{code:"CHC2D",name:"Canadian History Since WWI (Academic)"},{code:"CHC2P",name:"Canadian History Since WWI (Applied)"},{code:"CHV2O",name:"Civics and Citizenship"},{code:"ENG2D",name:"English (Academic)"},{code:"ENG2P",name:"English (Applied)"},{code:"EAE2D",name:"English (French OSSD, Academic)"},{code:"FSF2D",name:"French (Academic)"},{code:"FSF2P",name:"Core French (Applied)"},{code:"GLC2O",name:"Career Studies"},{code:"HIF2O",name:"Individual and Family Living"},{code:"HFN2O",name:"Food and Nutrition"},{code:"ICS2O",name:"Introduction to Computer Studies"},{code:"ICD2O",name:"Digital Technology & Innovations"},{code:"MPM2D",name:"Principles of Mathematics (Academic)"},{code:"MFM2P",name:"Foundations of Mathematics (Applied)"},{code:"PPL2O",name:"Healthy Active Living Education"},{code:"SNC2D",name:"Science (Academic)"},{code:"SNC2P",name:"Science (Applied)"},{code:"TEJ2O",name:"Computer Technology"},{code:"TFJ2O",name:"Hospitality and Tourism"},{code:"TDJ2O",name:"Technological Design"},{code:"TAS2O",name:"Technology and the Skilled Trades"}];

    const D11=[{code:"AVI3M",name:"Visual Arts (U/C)"},{code:"AVI3O",name:"Visual Arts (Open)"},{code:"ADA3M",name:"Drama (U/C)"},{code:"ADA3O",name:"Drama (Open)"},{code:"ASM3M",name:"Media Arts (U/C)"},{code:"ASM3O",name:"Media Arts (Open)"},{code:"BAF3M",name:"Financial Accounting Fundamentals"},{code:"BDP3O",name:"Entrepreneurship: The Enterprising Person"},{code:"BTA3O",name:"ICT: The Digital Environment"},{code:"BMI3C",name:"Marketing: Goods, Services, Events"},{code:"CIE3M",name:"The Individual and the Economy"},{code:"CGD3M",name:"Regional Geography"},{code:"CGF3M",name:"Forces of Nature"},{code:"CGT3O",name:"Intro to Spatial Technologies (Open)"},{code:"CGG3O",name:"Travel & Tourism"},{code:"CHA3U",name:"American History"},{code:"CHT3O",name:"World History since 1900 (Open)"},{code:"CLU3M",name:"Understanding Canadian Law"},{code:"CPC3O",name:"Politics in Action: Making Change"},{code:"ENG3C",name:"English (College)"},{code:"ENG3U",name:"English (University)"},{code:"EAE3U",name:"English (French OSSD, University)"},{code:"EPS3O",name:"Presentation and Speaking Skills"},{code:"FSF3U",name:"Core French (University)"},{code:"FSF3O",name:"Core French (Open)"},{code:"FEF3U",name:"Extended French (University)"},{code:"FIF3U",name:"French Immersion (University)"},{code:"FIF3O",name:"French Immersion (Open)"},{code:"GWL3O",name:"Designing Your Future"},{code:"HSG3M",name:"Gender Studies"},{code:"HFC3M",name:"Food and Culture"},{code:"HPW3C",name:"Working with Infants & Young Children"},{code:"HZB3M",name:"Philosophy: The Big Questions"},{code:"HRT3M",name:"World Religions"},{code:"HPC3O",name:"Raising Healthy Children"},{code:"HSP3U",name:"Intro to Anthropology (University)"},{code:"ICS3C",name:"Intro to Computer Programming (College)"},{code:"ICS3U",name:"Intro to Computer Science (University)"},{code:"IDC3O",name:"Sports & Entertainment Marketing (Open)"},{code:"MBF3C",name:"Foundations for College Mathematics"},{code:"MCF3M",name:"Functions and Applications"},{code:"MCR3U",name:"Functions"},{code:"NBE3U",name:"Understanding Contemporary First Nations, Métis, and Inuit"},{code:"PPL3O",name:"Healthy Active Living Education"},{code:"PPZ3C",name:"Health for Life"},{code:"SBI3C",name:"Biology (College)"},{code:"SBI3U",name:"Biology (University)"},{code:"SCH3U",name:"Chemistry (University)"},{code:"SPH3U",name:"Physics (University)"},{code:"SVN3M",name:"Environmental Science (U/C)"},{code:"SVN3E",name:"Environmental Science (Workplace)"},{code:"TGJ3M",name:"Communications Technology (U/C)"},{code:"TGJ3O",name:"Communications Tech: Broadcast/Print"},{code:"TDJ3M",name:"Technological Design (U/C)"},{code:"TDJ3O",name:"Technological Design and the Environment"}];

    const D12=[{code:"ADA4E",name:"Drama (Workplace)"},{code:"ADA4M",name:"Drama (U/C)"},{code:"AEA4O",name:"Exploring & Creating in the Arts"},{code:"ASM4E",name:"Media Arts (Workplace)"},{code:"AVI4M",name:"Visual Arts (U/C)"},{code:"BAT4M",name:"Financial Accounting Principles"},{code:"BDV4C",name:"Entrepreneurship: Venture Planning (C)"},{code:"BTX4C",name:"ICT: Multimedia Solutions (C)"},{code:"BBB4M",name:"International Business Fundamentals"},{code:"BOH4M",name:"Business Leadership"},{code:"CIA4U",name:"Analysing Current Economic Issues"},{code:"CGW4U",name:"World Issues (University)"},{code:"CGW4C",name:"World Issues (College)"},{code:"CGR4M",name:"Environment & Resource Management"},{code:"CGO4M",name:"Spatial Technologies in Action"},{code:"CGU4M",name:"World Geography: Urban Patterns"},{code:"CHY4U",name:"World History since 15th Century (U)"},{code:"CHY4C",name:"World History since 15th Century (C)"},{code:"CHI4U",name:"Canada: History, Identity & Culture"},{code:"CLN4U",name:"Canadian & International Law (U)"},{code:"CLN4C",name:"Legal Studies (College)"},{code:"CPW4U",name:"Canadian & International Politics"},{code:"ENG4U",name:"English (University)"},{code:"ENG4C",name:"English (College)"},{code:"EAE4U",name:"English (French OSSD, University)"},{code:"EWC4U",name:"The Writer’s Craft (University)"},{code:"EWC4C",name:"The Writer’s Craft (College)"},{code:"FSF4U",name:"Core French (University)"},{code:"FSF4O",name:"Core French (Open)"},{code:"HSE4M",name:"Equity & Social Justice"},{code:"HSC4M",name:"World Cultures"},{code:"HFA4U",name:"Nutrition & Health (University)"},{code:"HFA4C",name:"Nutrition & Health (College)"},{code:"HHS4U",name:"Families in Canada (University)"},{code:"HHS4C",name:"Families in Canada (College)"},{code:"HHG4M",name:"Human Development Through the Life Span"},{code:"HIP4O",name:"Personal Life Management"},{code:"ICS4U",name:"Computer Science (University)"},{code:"ICS4C",name:"Computer Programming (College)"},{code:"IDC4U",name:"Sports & Entertainment Marketing (U)"},{code:"IDC4O",name:"Sports & Entertainment Marketing (Open)"},{code:"MHF4U",name:"Advanced Functions"},{code:"MCV4U",name:"Calculus and Vectors"},{code:"MDM4U",name:"Mathematics of Data Management"},{code:"MCT4C",name:"Mathematics for College Technology"},{code:"MAP4C",name:"Foundations for College Mathematics"},{code:"OLC4O",name:"Ontario Secondary School Literacy Course"},{code:"PPL4O",name:"Healthy Active Living Education"},{code:"PLF4M",name:"Recreation & Healthy Active Living Leadership"},{code:"PSK4U",name:"Introductory Kinesiology"},{code:"SBI4U",name:"Biology (University)"},{code:"SCH4C",name:"Chemistry (College)"},{code:"SCH4U",name:"Chemistry (University)"},{code:"SPH4U",name:"Physics (University)"},{code:"SPH4C",name:"Physics (College)"},{code:"SES4U",name:"Earth and Space Science"},{code:"SNC4M",name:"Science (U/C)"},{code:"SNC4E",name:"Science (Workplace)"},{code:"TGJ4M",name:"Communications Technology (U/C)"},{code:"TGJ4O",name:"Communications Tech: Digital Imagery & Web"},{code:"TEJ4M",name:"Computer Engineering Technology (U/C)"},{code:"TFJ4E",name:"Hospitality & Tourism (Workplace)"},{code:"TFJ4C",name:"Hospitality & Tourism (College)"},{code:"TDJ4M",name:"Technological Design (U/C)"},{code:"TDJ4O",name:"Technological Design in the 21st Century"},{code:"TPJ4M",name:"Health Care (U/C)"}];

    const DESL=[{code:"ESLAO",name:"ESL Level 1"},{code:"ESLBO",name:"ESL Level 2"},{code:"ESLCO",name:"ESL Level 3"},{code:"ESLDO",name:"ESL Level 4"},{code:"ESLEO",name:"ESL Level 5"},{code:"ELDAO",name:"ELD Level 1"},{code:"ELDBO",name:"ELD Level 2"},{code:"ELDCO",name:"ELD Level 3"},{code:"ELDDO",name:"ELD Level 4"},{code:"ELDEO",name:"ELD Level 5"}];

    const ALL_COURSES=[...D9,...D10,...D11,...D12,...DESL];
    const POPULAR=new Set(["MHF4U","BBB4M","MCV4U","OLC4O","MAP4C","SBI4U","SPH4U","SCH4U","SCH4C","ENG4U","ENG4C","MDM4U","SBI3C","SBI3U","SPH3U","SCH3U","ENG3U","HHS4U","TEJ4M","TDJ4O","TFJ4C","HHG4M","PPL4O","SNC4M","CLN4U","FIF4U","CGW4M","BOH4M","AVI4M","HFA4U"]);
    const PRODUCT_BASE="https://canstemeducation.com/product/";
    const productURL=code=>PRODUCT_BASE+String(code||"").toLowerCase()+"/";

    /* ---------------- FULL SITE INDEX (Pages + Blogs) ---------------- */
    const SITE_ITEMS=[
      /* PAGES */
      {title:"About Us",url:"/about-2/",category:"Page",keywords:["about canstem","school info","contact team"]},
      {title:"Accessibility",url:"/accessibility/",category:"Page",keywords:["aoda","access","inclusive"]},
      {title:"Adult learning: Ontario high school diploma",url:"/adult-education/",category:"Page",keywords:["adult","ossd","ged","mature student"]},
      {title:"Attendance Policy",url:"/attendance-policy/",category:"Page",keywords:["policy","attendance","absent","late"]},
      {title:"Canadian Adult Education Credential CAEC-GED Canada",url:"/caec/",category:"Page",keywords:["caec","ged","adult credential"]},
      {title:"Career",url:"/career/",category:"Page",keywords:["jobs","hiring","careers"]},
      {title:"CELPIP",url:"/celpip/",category:"Page",keywords:["english test","celpip coaching"]},
      {title:"Cheating and Plagiarism",url:"/cheating-and-plagiarism/",category:"Page",keywords:["academic integrity","plagiarism policy"]},
      {title:"Code of Conduct",url:"/code-of-conduct/",category:"Page",keywords:["rules","behavior","conduct"]},
      {title:"SAT, PSAT, CELBAN, ACT, GRE, GMAT, CAEL",url:"/competitive-exam-prep/",category:"Page",keywords:["sat","psat","celban","act","gre","gmat","cael","prep"]},
      {title:"Computer Usage Policy",url:"/computer-usage-policy/",category:"Page",keywords:["technology policy","device","acceptable use"]},
      {title:"Course (Withdrawal / Change / Mode Switch) Request",url:"/change-request/",category:"Page",keywords:["withdrawal","course change","mode change"]},
      {title:"Contact Us",url:"/contact-us/",category:"Page",keywords:["contact","email","phone"]},
      {title:"Course Coding System",url:"/course-coding-system/",category:"Page",keywords:["course code","mhf4u","mcv4u","mapping"]},
      {title:"Course/Mode Change Terms & Policy",url:"/course-mode-change-terms-policy/",category:"Page",keywords:["mode change","online","in-class","terms"]},
      {title:"Examination Policy",url:"/examination-policy/",category:"Page",keywords:["exam","tests","rules"]},
      {title:"FAQ - Frequently Asked Questions",url:"/faq/",category:"Page",keywords:["faq","help","answers"]},
      {title:"Fee Structure 2025-26",url:"/fee-structure/",category:"Page",keywords:["fees","tuition","price"]},
      {title:"Final Exam and Report Card Information",url:"/final-exam-and-report-card-information/",category:"Page",keywords:["final exam","report card","transcript"]},
      {title:"Final Exam Request Form",url:"/final-exam-request/",category:"Page",keywords:["final exam request","online final exam","exam request form"]},
      {title:"Full-Time School in Canada (Grades 1-12)",url:"/full-time-school/",category:"Page",keywords:["full time","in-class","private school"]},
      {title:"Gallery",url:"/gallery/",category:"Page",keywords:["photos","campus","events"]},
      {title:"High School Credits",url:"/high-school-credits/",category:"Page",keywords:["credit courses","grade 9-12","online","in-class"]},
      {title:"High School for Healthcare",url:"/healthcare-programs/",category:"Page",keywords:["nursing prerequisites","healthcare"]},
      {title:"Holiday and Special Hours",url:"/holiday-and-special-hours/",category:"Page",keywords:["holiday","hours","schedule"]},
      {title:"Home",url:"/",category:"Page",keywords:["home","main"]},
      {title:"IELTS - International English Language Testing System",url:"/ielts/",category:"Page",keywords:["ielts test","coaching","prep"]},
      {title:"In-Class Student Instructions",url:"/in-class-student-instructions/",category:"Page",keywords:["instructions","in-class","students"]},
      {title:"International Students",url:"/international-student/",category:"Page",keywords:["study permit","visa","international"]},
      {title:"Late and Missed Assignments Policy",url:"/late-and-missed-assignments-policy/",category:"Page",keywords:["late policy","missed work"]},
      {title:"Live Links",url:"/live-links/",category:"Page",keywords:["links","resources"]},
      {title:"My Cart",url:"/cart/",category:"Page",keywords:["cart","checkout"]},
      {title:"Math Kangaroo Contest (Grades 1-12)",url:"/math-kangaroo-contest/",category:"Page",keywords:["math kangaroo","contest"]},

      /* NEW alias titles pointing to ILO page (as requested) */
      {title:"ILO-International Logic Olympiad | Where Global Minds Compete in Logic.",url:"/international-logic-olympiad/",category:"Page",keywords:["ILO","logic olympiad","international logic"]},

      {title:"Online High School",url:"/online-high-school/",category:"Page",keywords:["online credits","virtual school"]},
      {title:"Online Student Instructions",url:"/online-student-instructions/",category:"Page",keywords:["online","how to","start"]},
      {title:"Ontario High School Literacy",url:"/ontario-high-school-literacy/",category:"Page",keywords:["olc4o","osslt","literacy"]},
      {title:"Our Blog",url:"/our-blog/",category:"Page",keywords:["blog","articles"]},
      {title:"Our Blogs - CanSTEM",url:"/our-blogs-canstem/",category:"Page",keywords:["blog index"]},
      {title:"Payment",url:"/payment/",category:"Page",keywords:["credit","debit","amex","AMEX","secure","online payment","card","fees","payment",]},
      {title:"Secure Online Payment",url:"/online-payment/",category:"Page",keywords:["credit","debit","amex","AMEX","secure","online payment","card","fees","payment"]},
      {title:"Policies",url:"/policies/",category:"Page",keywords:["policies","rules"]},
      {title:"Privacy Policy",url:"/privacy-policy/",category:"Page",keywords:["privacy","policy"]},
      {title:"Promotions",url:"/promo/",category:"Page",keywords:["promo","offers"]},
      {title:"PTE English Language Tests | Pearson PTE",url:"/pte/",category:"Page",keywords:["pte","pearson","english test"]},
      {title:"Rating & Reviews",url:"/google-reviews/",category:"Page",keywords:["reviews","testimonials","google"]},
      {title:"Refer a Friend",url:"/refer-a-friend/",category:"Page",keywords:["referral","friend"]},
      {title:"Registration",url:"/registration/",category:"Page",keywords:["register","enroll","admission"]},
      {title:"Registration Forms",url:"/registration-forms/",category:"Page",keywords:["forms","registration"]},
      {title:"Safe School Policy",url:"/safe-school-policy/",category:"Page",keywords:["safe school","safety","policy"]},
      {title:"School Policies, Practices, and Procedures​",url:"/school-policies-practices-and-procedures/",category:"Page",keywords:["school policies","procedure"]},
      {title:"Student Support",url:"/student-services/",category:"Page",keywords:["student services","student support","help"]},
      {title:"OCAS Information and Updates",url:"/ocas/",category:"Page",keywords:["ocas","college applications","guidance"]},
      {title:"Ontario Secondary School Diploma (OSSD Requirements)",url:"/ossd/",category:"Page",keywords:["ossd","diploma","requirements"]},
      {title:"OUAC Guidance - 2025-2026 Schedule of Dates",url:"/ouac/",category:"Page",keywords:["ouac dates","schedule","ouac guidance"]},
      {title:"How-to Videos | Ontario Universities’ Application …",url:"/how-to-videos/",category:"Page",keywords:["video","how to","ouac"]},
      {title:"Sitemap",url:"/sitemap/",category:"Page",keywords:["sitemap","pages"]},
      {title:"STEM Programs",url:"/stem-programs/",category:"Page",keywords:["stem","programs","science tech"]},
      {title:"Student Achievement The Assessment and Evaluation Policy",url:"/student-achievement-the-assessment-and-evaluation-policy/",category:"Page",keywords:["assessment","evaluation","policy"]},
      {title:"Summer Camp",url:"/summer-camp/",category:"Page",keywords:["camp","summer"]},
      {title:"Summer School",url:"/summer-school/",category:"Page",keywords:["summer school","credits"]},
      {title:"TCF & TEF - Canada",url:"/tcf-and-tef/",category:"Page",keywords:["tcf","tef","french test"]},
      {title:"Terms & Conditions",url:"/terms-and-conditions/",category:"Page",keywords:["terms","conditions"]},
      {title:"Tutoring Services & Fees (Grades 1-12, IB, ESL, Test Prep, College)",url:"/tutoring/",category:"Page",keywords:["tutoring","fees","ib","esl","test prep"]},
      {title:"University Open House",url:"/university-open-house/",category:"Page",keywords:["open house","university"]},

      /* BLOGS */
      {title:"CanSTEM Principal Shortlisted Among Top 10 School Principals in Canada 2026 | Education Insider Canada",url:"/top-10-school-principals-in-canada-2026/",category:"Blog",keywords:["top 10","principal"]},
      {title:"What is the difference between CanSTEM and other private schools, online and in-person schools?",url:"/why-canstem-is-different/",category:"Blog",keywords:["comparison","schools"]},
      {title:"7 Easy Steps to a Successful Result",url:"/7-easy-steps-to-a-successful-result/",category:"Blog",keywords:["steps","success guide"]},
      {title:"Adult Learners Perspective on the Shift from GED Testing",url:"/adult-learners-perspective-on-the-shift-from-ged-testing/",category:"Blog",keywords:["adult","ged","caec"]},
      {title:"All-Inclusive Manual for Finishing the OUAC Application",url:"/all-inclusive-manual-for-finishing-the-ouac-application/",category:"Blog",keywords:["ouac","manual"]},
      {title:"Applying through the OUAC website",url:"/applying-through-the-ouac-website/",category:"Blog",keywords:["ouac","apply"]},
      {title:"Brampton’s Best High Schools",url:"/bramptons-best-high-schools/",category:"Blog",keywords:["brampton","ranking"]},
      {title:"Building a Successful Study Plan",url:"/building-a-successful-study-plan/",category:"Blog",keywords:["study plan"]},
      {title:"CanSTEM Education Private School Middle School Program Overview",url:"/canstem-education-private-school-middle-school-program-overview/",category:"Blog",keywords:["middle school"]},
      {title:"Exploring Modern Work Realities",url:"/exploring-modern-work-realities/",category:"Blog",keywords:["future of work"]},
      {title:"Exploring Possible Post-Secondary Careers",url:"/exploring-possible-post-secondary-careers/",category:"Blog",keywords:["careers"]},
      {title:"Forward-Looking Summer Courses in 2024",url:"/forward-looking-summer-courses-in-2024/",category:"Blog",keywords:["summer 2024"]},
      {title:"Handling the OSSD’s Requirements",url:"/handling-the-ossds-requirements/",category:"Blog",keywords:["ossd"]},
      {title:"How to Achieve Academic Success in High School",url:"/how-to-achieve-academic-success-in-high-school/",category:"Blog",keywords:["success","study"]},
      {title:"Making a Successful Exam Study Plan",url:"/making-a-successful-exam-study-plan/",category:"Blog",keywords:["exam plan"]},
      {title:"Manual for Post-Secondary Education",url:"/manual-for-post-secondary-education/",category:"Blog",keywords:["post-secondary"]},
      {title:"Mississauga Night Schools: Unlock Your Potential",url:"/mississauga-night-schools-unlock-your-potential/",category:"Blog",keywords:["night school"]},
      {title:"Online Students’ Guide to Back-to-School in 2024",url:"/online-students-guide-to-back-to-school-in-2024/",category:"Blog",keywords:["online"]},
      {title:"Ontario Students 2024 Graduation Requirements",url:"/2024-high-school-graduation-requirements/",category:"Blog",keywords:["requirements"]},
      {title:"Ontario Updates Secondary School Graduation Standards",url:"/ontario-updates-secondary-school-graduation-standards/",category:"Blog",keywords:["standards"]},
      {title:"Ontario’s grad requirements with tech credits and online access",url:"/ontarios-grad-requirements-with-tech-credits-and-online-access/",category:"Blog",keywords:["tech credits"]},
      {title:"OUAC & OCAS Applications",url:"/ouac-ocas-applications/",category:"Blog",keywords:["ouac","ocas"]},
      {title:"OUAC Program for Students in Ontario",url:"/ouac-program-for-students-in-ontario/",category:"Blog",keywords:["ouac program"]},
      {title:"Planning for Summer 2024 College and University Admission",url:"/planning-for-summer-2024-college-and-university-admission/",category:"Blog",keywords:["admission planning"]},
      {title:"The Ultimate Guide to Tutoring and Language Coaching in Mississauga & Brampton",url:"/tutoring-language-coaching-mississauga-brampton/",category:"Blog",keywords:["tutoring","language coaching"]},
      {title:"Private School vs Public School in Ontario: Which is Better for Your Child’s Future?",url:"/private-vs-public-school-ontario/",category:"Blog",keywords:["private vs public"]},
      {title:"Enrollment Now Open for Full-Time School 2025-26 in Ontario — Grades 1 to 12 at CanSTEM Education",url:"/enrollment-open-full-time-school-ontario-2025-26/",category:"Blog",keywords:["enrollment 2025"]},
      {title:"OSSD Requirements 2025: Your Complete Guide to Ontario Secondary School Diploma Success",url:"/ossd-requirements-2025/",category:"Blog",keywords:["ossd 2025"]},
      {title:"Why CanSTEM? The Private School in Brampton That Puts Students First",url:"/why-canstem/",category:"Blog",keywords:["why canstem"]},
      {title:"Summer Camp 2025 at CanSTEM",url:"/summer-camp-2025-at-canstem/",category:"Blog",keywords:["camp 2025"]},
      {title:"Summer School 2025 at CanSTEM",url:"/summer-school-2025-at-canstem/",category:"Blog",keywords:["summer school 2025"]},
      {title:"Best Coaching for PTE, IELTS, CELPIP & CAEL in Canada",url:"/english-test-coaching-canada/",category:"Blog",keywords:["pte","ielts","celpip","cael"]},
      {title:"The Role of AI in Modern Education",url:"/the-role-of-ai-in-modern-education/",category:"Blog",keywords:["ai","education"]},
      {title:"The Role of Parents and Families in Online Schooling",url:"/the-role-of-parents-and-families-in-online-schooling/",category:"Blog",keywords:["parents","online"]},
      {title:"2024 High School Graduation Requirements",url:"/2024-high-school-graduation-requirements/",category:"Blog",keywords:["graduation 2024"]},
      {title:"CanSTEM Education Private School",url:"/canstem-education-private-school-3/",category:"Blog",keywords:["overview"]},
      {title:"The Benefits of One on One Tutoring Services",url:"/the-benefits-of-one-on-one-tutoring-services/",category:"Blog",keywords:["tutoring benefits"]},
      {title:"The Value of Private School Education",url:"/the-value-of-private-school-education/",category:"Blog",keywords:["value"]},
      {title:"Dual Enrollment Explained - College and High School Credits",url:"/dual-enrollment-explained-college-and-high-school-credits/",category:"Blog",keywords:["dual enrollment"]},
      {title:"5 Things To Consider Before Changing Schools For Your Child",url:"/5-things-to-consider-before-changing-schools-for-your-child/",category:"Blog",keywords:["changing schools"]},
      {title:"5 Benefits of Earning College Credit in High School",url:"/5-benefits-of-earning-college-credit-in-high-school/",category:"Blog",keywords:["college credit"]},
      {title:"How Raising Pets Can Help Children Develop Emotional Intelligence",url:"/raising-pets-can-help-children-develop-emotional-intelligence/",category:"Blog",keywords:["pets","emotional intelligence"]},
      {title:"5 Ways A Tutor Can Help Students With Exceptionalities",url:"/5-ways-a-tutor-can-help-students-with-exceptionalities/",category:"Blog",keywords:["exceptionalities"]},
      {title:"The Best Way to Balance Study and Fun over the Holidays",url:"/the-best-way-to-balance-study-and-fun-over-the-holidays/",category:"Blog",keywords:["holidays","balance"]},
      {title:"How to Stop Your Child from Hating School",url:"/how-to-stop-your-child-from-hating-school/",category:"Blog",keywords:["motivation"]},
      {title:"Perspectives on Screen Time in a Pandemic",url:"/perspectives-on-screen-time-in-a-pandemic/",category:"Blog",keywords:["screen time"]},
      {title:"What to Wear to a Private School Parent Interview",url:"/what-to-wear-to-a-private-school-parent-interview/",category:"Blog",keywords:["interview"]},
      {title:"Four Steps to Selecting a School for Your Child",url:"/four-steps-to-selecting-a-school-for-your-child/",category:"Blog",keywords:["selection steps"]},
      {title:"Tips To Choose A Tutor",url:"/tips-to-choose-a-tutor/",category:"Blog",keywords:["choose tutor"]},
      {title:"Tips for Starting High School",url:"/tips-for-starting-high-school/",category:"Blog",keywords:["starting high school"]},
      {title:"Boost Grades and Achieve Academic Excellence",url:"/boost-grades-and-achieve-academic-excellence/",category:"Blog",keywords:["grades"]},
      {title:"University Scholarship Applications for 2024-2025",url:"/university-scholarship-applications-for-2024-2025/",category:"Blog",keywords:["scholarship"]},
      {title:"The Importance of Early Preparation for University",url:"/the-importance-of-early-preparation-for-university/",category:"Blog",keywords:["early prep"]},
      {title:"Empowering Mature Students",url:"/empowering-mature-students/",category:"Blog",keywords:["mature students"]},
      {title:"Nursing Prerequisite Courses at CanSTEM Education",url:"/nursing-prerequisite-courses-at-canstem-education/",category:"Blog",keywords:["nursing"]},
      {title:"Online Learning at CanSTEM: Study Anytime, Anywhere",url:"/online-learning-at-canstem/",category:"Blog",keywords:["online learning"]},
      {title:"Why November 11 Matters: Remembrance Day in Canada",url:"/remembrance-day-in-canada/",category:"Blog",keywords:["remembrance day"]},
      {title:"Impact of IRCC Closing the Student Direct Stream",url:"/impact-of-ircc-closing-the-student-direct-stream/",category:"Blog",keywords:["ircc","sds"]},
      /* NEW blog */
      {title:"International Logic Olympiad (ILO) 2026 in Canada | CanSTEM Official Representative",url:"/international-logic-olympiad-canada-canstem/",category:"Blog",keywords:["ILO","logic olympiad","2026","canada"]},

      {title:"How Raising Pets Can Help Children Develop Emotional Intelligence (duplicate entry)",url:"/how-raising-pets-can-help-children-develop-emotional-intelligence/",category:"Blog",keywords:["pets","emotion"]},
      {title:"Navigating COVID",url:"/navigating-covid/",category:"Blog",keywords:["covid"]},
      {title:"How Private Schools Influence Academic Success and Lifelong Outcomes",url:"/academic-success-and-lifelong-outcomes/",category:"Blog",keywords:["private school","outcomes"]}
    ];

    function gradeLabelForCode(code,name){
      const gChar=code?.[3];
      if(/[1-4]/.test(gChar)){ const g=8+Number(gChar); return `Grade ${g}`; }
      if(/^(ESL|ELD)/.test(code)) return name;
      return "";
    }

    const toCourseItem=c=>({
      kind:"course",
      title:`${c.code} — ${gradeLabelForCode(c.code,c.name)}`,
      code:c.code, name:c.name, url:productURL(c.code),
      chip:"Course", chipClass:"course",
      haystack:(c.code+" "+c.name).toLowerCase(),
      popular:POPULAR.has(c.code)
    });

    const toSiteItem=p=>({
      kind:"content",
      title:p.title, code:p.title, name:"",
      url:p.url, chip:p.category, chipClass:p.category==="Blog"?"blog":"page",
      haystack:(p.title+" "+(p.keywords||[]).join(" ")).toLowerCase()
    });

    const COURSE_ITEMS=ALL_COURSES.map(toCourseItem);
    const SITE_INDEX_ITEMS=SITE_ITEMS.map(toSiteItem);

    function searchAll(q){
      const t=q.trim().toLowerCase(); if(!t) return [];
      const out=[];
      for(const it of COURSE_ITEMS){
        const code=it.code.toLowerCase(), name=it.name.toLowerCase(); let s=null;
        if(code.startsWith(t)) s=0; else if(code.includes(t)) s=1; else if(name.includes(t)) s=2;
        if(s!==null) out.push({...it,score:s});
      }
      for(const it of SITE_INDEX_ITEMS){
        const title=it.title.toLowerCase(); let s=null;
        if(title.startsWith(t)) s=0.5; else if(title.includes(t)) s=1.5; else if(it.haystack.includes(t)) s=2.5;
        if(s!==null) out.push({...it,score:s});
      }
      out.sort((a,b)=>a.score!==b.score?a.score-b.score:a.kind!==b.kind?(a.kind==="course"?-1:1):(a.title||"").localeCompare(b.title||""));
      return out.slice(0,30);
    }

    const input=document.querySelector("#csbx-q");
    const suggest=document.querySelector("#csbx-suggest");

    function setOpen(on){ input.setAttribute("aria-expanded", on?"true":"false"); suggest.classList.toggle("csbx-show", on); }
    function makeSection(t){ const s=document.createElement("div"); s.className="csbx-section"; const h=document.createElement("div"); h.className="csbx-section-title"; h.textContent=t; s.appendChild(h); return s; }

    function render(){
      const results=searchAll(input.value);
      suggest.innerHTML="";
      if(!results.length){ setOpen(false); return; }

      const courses=results.filter(r=>r.kind==="course");
      const pages=results.filter(r=>r.kind==="content" && r.chip==="Page");
      const blogs=results.filter(r=>r.kind==="content" && r.chip==="Blog");

      const add=(label,items)=>{
        if(!items.length) return;
        const section=makeSection(label);
        items.forEach((it,idx)=>{
          const row=document.createElement("div");
          row.className="csbx-row"; row.id=`${label.toLowerCase()}-${idx}`; row.role="option";
          row.setAttribute("aria-selected","false");
          const second=it.kind==="course"?it.name:"";
          row.innerHTML=`<div class="csbx-left"><span class="csbx-code">${it.title}</span><span class="csbx-name">${second}</span></div><div class="csbx-right"><span class="csbx-chip ${it.chipClass}">${it.chip}</span>${it.popular?'<span class="csbx-chip course is-popular">Popular</span>':''}</div>`;
          row.addEventListener("mousedown",e=>e.preventDefault());
          row.addEventListener("click",()=>location.href=it.url);
          section.appendChild(row);
        });
        suggest.appendChild(section);
      };

      add("Courses",courses);
      add("Pages",pages);
      add("Blogs",blogs);

      activeIndex=-1;
      setOpen(true);
    }

    let activeIndex=-1;
    input.addEventListener("input",render);
    input.addEventListener("focus",()=>{ if(input.value.trim()) render(); });
    input.addEventListener("blur",()=>setTimeout(()=>setOpen(false),140));
    input.addEventListener("keydown",e=>{
      const items=[...suggest.querySelectorAll(".csbx-row")];
      if(!items.length){
        if(e.key==="Enter" && input.value.trim()){
          e.preventDefault();
          location.href = productURL(input.value.trim().toUpperCase());
        }
        return;
      }
      if(e.key==="ArrowDown"){ e.preventDefault(); activeIndex=Math.min(activeIndex+1,items.length-1); }
      else if(e.key==="ArrowUp"){ e.preventDefault(); activeIndex=Math.max(activeIndex-1,0); }
      else if(e.key==="Enter"){ e.preventDefault(); (items[activeIndex]||items[0])?.click(); }
      else if(e.key==="Escape"){ setOpen(false); return; }
      else { return; }

      items.forEach(n=>n.setAttribute("aria-selected","false"));
      if(activeIndex>=0 && items[activeIndex]){
        items[activeIndex].setAttribute("aria-selected","true");
        items[activeIndex].scrollIntoView({block:"nearest"});
      }
    });
  })();
</script>

<?php
if ( astra_page_layout() === 'right-sidebar' ) { get_sidebar(); }
echo do_shortcode("[hfe_template id='842']");
get_footer();
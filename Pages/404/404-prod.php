<?php
if ( ! defined('ABSPATH') ) { exit; }
get_header();
echo do_shortcode("[hfe_template id='1642']");
?>
<style>
  body.error404 .site-header,
  body.error404 header#masthead,
  body.error404 .site-footer,
  body.error404 footer#colophon { display:none !important; }

  body.error404 .ast-container,
  body.error404 .site-content .ast-container,
  body.error404 .ast-container-fluid {
    max-width:100% !important;
    margin-left:auto !important;
    margin-right:auto !important;
    padding-left:0 !important;
    padding-right:0 !important;
    box-sizing:border-box;
  }
  @media (min-width:922px){
    body.error404 .site-content .ast-container { display:inline-block !important; width:100%; }
  }

  #canstem-404{
    --head:#001161; --sub:#00427c; --ink:#26262B; --border:#e2e8f0; --bg:#f6f9ff;
    font-family:"Open Sans",system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;
    color:var(--ink); text-align:center;
    padding:16px 16px 24px;
    background: radial-gradient(1200px 500px at 50% -200px, #eaf2ff 0%, transparent 70%) , linear-gradient(180deg, #ffffff 0%, #ffffff 100%);
    position:relative; overflow:hidden;
  }
  #canstem-404 .decor-book{
    position:absolute; right:5%; top:8%;
    width:140px; height:auto; opacity:.08; transform: rotate(6deg);
    animation: floaty 6s ease-in-out infinite;
    pointer-events:none;
  }
  @keyframes floaty { 0%,100%{ transform:translateY(0) rotate(6deg);} 50%{ transform:translateY(-6px) rotate(6deg);} }
  @media (prefers-reduced-motion:reduce){ #canstem-404 .decor-book{ animation:none; } }

  #canstem-404 .wrap{
    width:100%; max-width:720px; margin:12px auto 24px;
    background:#fff; border:1px solid var(--border); border-radius:16px;
    padding:28px 20px; box-shadow:0 8px 24px rgba(0,0,0,.06);
    animation: cardIn .4s ease both;
  }
  @keyframes cardIn { from{ opacity:0; transform: translateY(8px); } to{ opacity:1; transform:none; } }

  #canstem-404 h1{
    margin:0 0 8px; color:var(--head); font-weight:900;
    font-size:clamp(36px,5.5vw,56px);
  }
  #canstem-404 p{
    margin:0 0 18px; font-size:clamp(15px,2vw,18px); color:#4a4f57;
  }

  #canstem-404 .actions{
    display:flex; flex-wrap:wrap; justify-content:center; gap:10px;
  }
  #canstem-404 .btn{
    display:inline-flex; align-items:center; justify-content:center; gap:8px;
    padding:12px 18px; border-radius:10px; text-decoration:none; font-weight:800; letter-spacing:.01em;
    font-size:15px; line-height:1; box-shadow:0 2px 10px rgba(0,0,0,.06);
    transition: transform .06s ease, filter .2s ease, background-color .2s ease, color .2s ease, border-color .2s ease;
  }
  #canstem-404 .btn:active{ transform: translateY(1px); }
  #canstem-404 .btn-primary{
    background:var(--sub); color:#fff; border:1px solid #003766;
  }
  #canstem-404 .btn-primary:hover{ filter: brightness(1.06); }
  #canstem-404 .btn-ghost{
    background:#fff; color:#0a0a0a; border:1px solid var(--border);
  }
  #canstem-404 .btn-ghost:hover{ background:#f7fbff; border-color:#c7d2fe; }

  .footer-separator{
    position:relative; height:42px; background:linear-gradient(180deg,#eff6ff 0%, #ffffff 90%);
  }
  .footer-separator:before{
    content:""; position:absolute; left:50%; transform:translateX(-50%);
    top:-22px; width:70px; height:44px; background:#eff6ff; border-top-left-radius:80px 60px; border-top-right-radius:80px 60px;
    box-shadow:0 -6px 0 #0a2f6d;
  }
  .footer-separator:after{
    content:""; position:absolute; left:0; right:0; top:18px; height:4px; background:#0a2f6d;
  }

  @media (max-width:767.98px){
    #canstem-404 .wrap{ max-width:560px; padding:24px 18px; }
    #canstem-404 .actions .btn{ min-width:170px; }
  }
  @media (min-width:1024px){ #canstem-404 .wrap{ max-width:780px; } }
</style>

<?php if ( astra_page_layout() === 'left-sidebar' ) { get_sidebar(); } ?>

<div id="primary" <?php astra_primary_class(); ?>>
  <?php astra_primary_content_top(); ?>
  <section id="canstem-404" role="main" aria-labelledby="canstem-404-title">
    <svg class="decor-book" viewBox="0 0 128 128" aria-hidden="true" focusable="false">
      <path d="M20 20h60a10 10 0 0 1 10 10v68a6 6 0 0 1-6 6H24a8 8 0 0 1-8-8V26a6 6 0 0 1 6-6z" fill="#00427c"/>
      <path d="M28 28h60a10 10 0 0 1 10 10v64" fill="none" stroke="#001161" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
      <path d="M24 38h52M24 54h52M24 70h40" stroke="#fff" stroke-width="6" stroke-linecap="round" opacity=".7"/>
    </svg>

    <div class="wrap">
      <h1 id="canstem-404-title">404</h1>
      <p>The page you requested does not exist</p>
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

<?php
if ( astra_page_layout() === 'right-sidebar' ) { get_sidebar(); }
echo do_shortcode("[hfe_template id='842']");
get_footer();
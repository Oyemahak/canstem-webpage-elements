<?php
if (! defined('ABSPATH')) {
    exit;
}
get_header();
echo do_shortcode('[hfe_template id="1642"]');
?>
<style>
    .site-header,
    header#masthead,
    .site-footer,
    footer#colophon {
        display: none !important;
    }

    body.error404 .ast-container,
    body.error404 .site-content .ast-container {
        max-width: 100% !important;
        margin-left: auto !important;
        margin-right: auto !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
        box-sizing: border-box;
    }

    @media (max-width: 921.98px) {
        body.error404 .ast-container {
            max-width: 100% !important;
            padding-left: 16px !important;
            padding-right: 16px !important;
        }
    }

    @media (min-width: 922px) {
        body.error404 .site-content .ast-container {
            display: inline-block !important;
        }
    }

    #canstem-404 {
        --head: #001161;
        --sub: #00427c;
        --ink: #26262B;
        --border: #e2e8f0;
        font-family: "Open Sans", system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
        color: var(--ink);
        text-align: center;
        padding: 48px;
    }

    #canstem-404 .wrap {
        width: 100%;
        max-width: 420px;
        margin: 0 auto;
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 32px 20px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, .05);
    }

    #canstem-404 h1 {
        margin: 0 0 16px;
        color: var(--head);
        font-weight: 900;
        font-size: 64px;
    }


    #canstem-404 p {
        margin: 0 0 24px;
        font-size: clamp(16px, 2.2vw, 22px);
        color: #404047
    }

    #canstem-404 .actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 12px;
        margin-top: 10px
    }

    #canstem-404 .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 24px;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 700;
        font-size: 16px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, .06);
        transition: transform .05s, filter .2s, background-color .2s, color .2s, border-color .2s
    }

    #canstem-404 .btn:active {
        transform: translateY(1px)
    }

    #canstem-404 .btn-ghost {
        background: #fff;
        color: #000;
        border: 1px solid var(--border)
    }

    #canstem-404 .btn-ghost:hover {
        background: #f7fbff;
        border-color: #c7d2fe
    }

    #canstem-404 .btn-primary {
        background: var(--sub);
        color: #fff;
        border: 1px solid #003766
    }

    #canstem-404 .btn-primary:hover {
        filter: brightness(1.08)
    }

    @media (min-width:480px) {
        #canstem-404 {
            padding: 24px;
        }

        #canstem-404 .wrap {
            max-width: 520px;
            padding: 36px 22px;
        }

        #canstem-404 .btn {
            min-width: 200px;
        }
    }

    @media (min-width:768px) {
        #canstem-404 {
            padding: 32px;
        }

        #canstem-404 .wrap {
            max-width: 640px;
            padding: 44px 28px;
        }

        #canstem-404 .btn {
            font-size: 16px;
            padding: 12px 20px;
        }
    }

    @media (min-width:1024px) {
        #canstem-404 .wrap {
            max-width: 760px;
        }
    }

    @media (min-width:1280px) {
        #canstem-404 .wrap {
            max-width: 820px;
        }
    }
</style>

<?php if (astra_page_layout() === 'left-sidebar') {
    get_sidebar();
} ?>

<div id="primary" <?php astra_primary_class(); ?>>
    <?php astra_primary_content_top(); ?>
    <section id="canstem-404" role="main" aria-labelledby="canstem-404-title">
        <div class="wrap">
            <h1 id="canstem-404-title">404</h1>
            <p>The page you requested does not exist</p>
            <div class="actions">
                <a class="btn btn-ghost" href="<?php echo esc_url(home_url('/')); ?>">BACK TO HOME</a>
                <a class="btn btn-ghost" href="https://registration.ca.powerschool.com/family/gosnap.aspx?action=100000869&amp;culture=en" target="_blank" rel="noopener">ANY QUESTIONS?</a>
                <a class="btn btn-primary" href="https://registration.ca.powerschool.com/family/gosnap.aspx?action=100000879&amp;culture=en" target="_blank" rel="noopener">ENROLL NOW!</a>
            </div>
        </div>
    </section>
    <?php astra_primary_content_bottom(); ?>
</div>

<?php
if (astra_page_layout() === 'right-sidebar') {
    get_sidebar();
}
echo do_shortcode('[hfe_template id="842"]');
get_footer();

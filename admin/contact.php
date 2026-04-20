<?php
/**
 * Contact/Help Page
 * SDO ALPAS - Schools Division Office Authority to Travel, Locator and Pass slip Approval System
 */

require_once __DIR__ . '/../includes/header.php';
?>

<style>
    .label-mobile {
        display: none;
    }

    @media (max-width: 576px) {
        .contact-cards-wrap {
            margin: 18px auto !important;
            padding: 0 8px !important;
        }

        .contact-cards-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 8px !important;
        }

        .contact-cards-grid .detail-card {
            border-radius: 10px;
            overflow: hidden;
        }

        .contact-cards-grid .detail-card-header {
            padding: 10px 10px;
        }

        .contact-cards-grid .detail-card-header h3 {
            font-size: 0.92rem;
            line-height: 1.2;
        }

        .contact-cards-grid .detail-card-body {
            padding: 14px 10px !important;
        }

        .contact-cards-grid .detail-card-body h4 {
            font-size: 1rem !important;
            line-height: 1.25;
            margin-bottom: 10px !important;
        }

        .contact-cards-grid .detail-card-body p {
            font-size: 0.82rem;
            line-height: 1.35;
            margin-bottom: 12px !important;
            display: -webkit-box;
            -webkit-line-clamp: 5;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .contact-cards-grid .detail-card-body i {
            font-size: 1.85rem !important;
            margin-bottom: 10px !important;
        }

        .contact-cards-grid .detail-card-body a {
            width: 100%;
            justify-content: center;
            padding: 9px 8px !important;
            font-size: 0.82rem !important;
            gap: 6px !important;
            line-height: 1.2;
        }

        .contact-cards-grid .detail-card-body a i {
            font-size: 0.95rem !important;
            margin-bottom: 0 !important;
        }

        .label-desktop {
            display: none;
        }

        .label-mobile {
            display: inline;
        }
    }
</style>

<div class="contact-cards-wrap" style="max-width: 1200px; margin: 40px auto; padding: 0 16px;">
    <div class="contact-cards-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px; align-items: stretch;">
        <div class="detail-card" style="margin: 0; height: 100%;">
            <div class="detail-card-header">
                <h3><i class="fas fa-headset"></i> Need Help?</h3>
            </div>
            <div class="detail-card-body" style="text-align: center; padding: 40px 20px;">
                <i class="fas fa-question-circle"
                    style="font-size: 4rem; color: var(--primary-color, #2563eb); margin-bottom: 20px;"></i>
                <h4 style="margin-bottom: 15px; font-size: 1.5rem;">
                    <span class="label-desktop">ICT Helpdesk Support</span>
                    <span class="label-mobile">ICT Helpdesk</span>
                </h4>
                <p style="margin-bottom: 30px; color: var(--text-secondary, #64748b); line-height: 1.6;">
                    For technical difficulties and system concerns, connect with our ICT Helpdesk through the support portal.
                </p>
                <a href="https://wfh-sdospc.com/ICTHelpdesk-Online/login.php" target="_blank" rel="noopener noreferrer" class="btn btn-primary"
                    style="display: inline-flex; align-items: center; gap: 10px; padding: 12px 24px; font-size: 1.1rem; text-decoration: none;">
                    <i class="fas fa-external-link-alt"></i>
                    <span class="label-desktop">Connect with Us</span>
                    <span class="label-mobile">Connect</span>
                </a>
            </div>
        </div>

        <div class="detail-card" style="margin: 0; height: 100%;">
            <div class="detail-card-header">
                <h3>
                    <i class="fas fa-star"></i>
                    <span class="label-desktop">Client Satisfaction</span>
                    <span class="label-mobile">Survey</span>
                </h3>
            </div>
            <div class="detail-card-body" style="text-align: center; padding: 40px 20px;">
                <i class="fas fa-star" style="font-size: 4rem; color: #10b981; margin-bottom: 20px;"></i>
                <h4 style="margin-bottom: 15px; font-size: 1.95rem;">
                    <span class="label-desktop">Client Satisfaction Measurement</span>
                    <span class="label-mobile">Client Survey</span>
                </h4>
                <p style="margin-bottom: 30px; color: var(--text-secondary, #64748b); line-height: 1.6; max-width: 540px; margin-left: auto; margin-right: auto;">
                    Your feedback helps us improve the LDP Passbook System. Please share your experience through our survey.
                </p>
                <a href="https://wfh-sdospc.com/csm/csm.php" target="_blank" rel="noopener noreferrer"
                    style="display: inline-flex; align-items: center; gap: 10px; padding: 12px 24px; font-size: 1.1rem; text-decoration: none; border-radius: 10px; background-color: #10b981; color: #ffffff; font-weight: 600;">
                    <i class="fas fa-clipboard-check"></i>
                    <span class="label-desktop">Take the Survey</span>
                    <span class="label-mobile">Survey</span>
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
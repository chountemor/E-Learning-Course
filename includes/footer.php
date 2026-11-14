<?php
/**
 * eLearning College — Premium responsive footer
 */
?>
<footer class="footer" id="footer">
    <div class="footer-glow footer-glow-one"></div>
    <div class="footer-glow footer-glow-two"></div>

    <div class="container footer-container">
<!-- 
        <div class="footer-cta">
            <div class="footer-cta-copy">
                <span class="footer-kicker"><?= t('KEEP LEARNING','បន្តការសិក្សា') ?></span>
                <h2><?= t('Your next skill starts here.','ជំនាញថ្មីរបស់អ្នក ចាប់ផ្តើមពីទីនេះ។') ?></h2>
                <p><?= t('Join eLearning College and learn practical skills for free, anytime and anywhere.','ចូលរួមជាមួយ eLearning College ដើម្បីរៀនជំនាញជាក់ស្តែងដោយឥតគិតថ្លៃ គ្រប់ពេល និងគ្រប់ទីកន្លែង។') ?></p>
            </div>
            <a class="footer-cta-btn" href="<?= url('courses.php') ?>">
                <?= t('Browse Courses','មើលវគ្គសិក្សា') ?>
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div> -->

        <div class="footer-grid">
            <!-- Brand -->
            <div class="footer-column footer-brand">
                <a href="<?= url('index.php') ?>" class="footer-brand-link footer-logo-link" aria-label="eLearning College">
                    <img class="site-logo footer-site-logo" src="<?= url('assets/images/logo.svg') ?>" alt="eLearning College">
                </a>

                <p class="footer-tagline"><?= t('Learn today. Lead tomorrow.','រៀនថ្ងៃនេះ ដឹកនាំអនាគត។') ?></p>
                <p class="footer-description">
                    <?= t('A modern free online learning platform designed to help everyone learn new skills, improve knowledge, and build a better future.','វេទិកាសិក្សាតាមអនឡាញទំនើប និងឥតគិតថ្លៃ ដែលជួយអ្នករៀនជំនាញថ្មី បង្កើនចំណេះដឹង និងកសាងអនាគតកាន់តែប្រសើរ។') ?>
                </p>

                <div class="footer-social" aria-label="Social media">
                    <a href="https://www.facebook.com/profile.php?id=100074312918658" class="social-btn" aria-label="Facebook" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="https://t.me/Phearun_Cambodia" class="social-btn" aria-label="Telegram" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-telegram"></i></a>
                    <a href="https://www.youtube.com/@KheangPhearun-i8g" class="social-btn" aria-label="YouTube" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-youtube"></i></a>
                    <a href="https://www.tiktok.com/@kheangphearon515?is_from_webapp=1&sender_device=pc" class="social-btn" aria-label="TikTok" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-tiktok"></i></a>
                </div>
            </div>

            <!-- Explore -->
            <div class="footer-column">
                <h3 class="footer-title"><?= t('Explore','ស្វែងរក') ?></h3>
                <ul class="footer-menu">
                    <li><i class="fa-solid "></i><?= t('Home','ទំព័រដើម') ?></li>
                    <li><i class="fa-solid "></i><?= t('All Courses','វគ្គសិក្សាទាំងអស់') ?></li>
                    <li><i class="fa-solid "></i><?= t('Diplomas & Certificates','សញ្ញាបត្រ និងវិញ្ញាបនបត្រ') ?></li>
                    <li><i class="fa-solid "></i><?= t('My Learning','ការសិក្សារបស់ខ្ញុំ') ?></li>
                </ul>
            </div>

            <!-- Account -->
            <div class="footer-column">
                <h3 class="footer-title"><?= t('Account','គណនី') ?></h3>
                <ul class="footer-menu">
                    <li><i class="fa-solid "></i><?= t('Sign In','ចូលប្រព័ន្ធ') ?></li>
                    <li><i class="fa-solid "></i><?= t('Create Account','បង្កើតគណនី') ?></li>
                    <li><i class="fa-solid "></i><?= t('My Learning','ការសិក្សារបស់ខ្ញុំ') ?></li>
                    <li><i class="fa-solid "></i><?= t('Achievements','សមិទ្ធផល') ?></li>
                </ul>
            </div>

            <!-- Contact -->
            <div class="footer-column footer-contact">
                <h3 class="footer-title"><?= t('Contact Us','ទំនាក់ទំនង') ?></h3>
                <div class="contact-list">
                    <a class="contact-item" href="tel:+855 77 695 894">
                        <span class="contact-icon"><i class="fa-solid fa-phone"></i></span>
                        <span class="contact-content"><small><?= t('Phone','ទូរស័ព្ទ') ?></small><strong>+855 77 695 894</strong></span>
                    </a>
                    <a class="contact-item" href="mailto:phearun@example.com">
                        <span class="contact-icon"><i class="fa-solid fa-envelope"></i></span>
                        <span class="contact-content"><small><?= t('Email','អ៊ីមែល') ?></small><strong>phearun@example.com</strong></span>
                    </a>
                    <div class="contact-item">
                        <span class="contact-icon"><i class="fa-solid fa-location-dot"></i></span>
                        <span class="contact-content"><small><?= t('Location','ទីតាំង') ?></small><strong>Phnom Penh, Cambodia</strong></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="container footer-bottom-content">
            <p>&copy; <?= date('Y') ?> <strong>eLearning College</strong>. <?= t('All Rights Reserved.','រក្សាសិទ្ធិគ្រប់យ៉ាង។') ?></p>
            <div class="footer-bottom-links">
                <a href="#"><?= t('Privacy Policy','គោលការណ៍ឯកជនភាព') ?></a>
                <span>•</span>
                <a href="#"><?= t('Terms & Conditions','លក្ខខណ្ឌប្រើប្រាស់') ?></a>
            </div>
        </div>
    </div>
</footer>

<button type="button" class="back-to-top" id="backToTop" aria-label="Back to top" title="Back to top">
    <i class="fa-solid fa-arrow-up"></i>
</button>
<script src="<?= function_exists('url') ? url('assets/js/footer.js') : 'assets/js/footer.js' ?>"></script>

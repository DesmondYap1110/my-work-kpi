<footer id="footer-section">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div id="footer-info-div">
                    <p id="footer-p">© Copyright <?php echo Date("Y"); ?> <?php echo $base_name; ?>. All Rights Reserved.</p>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- MOBILE MENU SEECTION -->
<div id="mm-section">
    <ul>
        <li class="list">
            <a href="<?php echo $base_url_dashboard; ?>/position.php">
                <span id="mm-icon"><i class="ri-user-settings-line"></i></span>
                <span id="mm-text">Position</span>
            </a>
        </li>
        <li class="list">
            <a href="<?php echo $base_url_dashboard; ?>/team.php">
                <span id="mm-icon"><i class="bx ri-team-line"></i></span>
                <span id="mm-text">Team</span>
            </a>
        </li>
        <li class="list" id="active">
            <a href="<?php echo $base_url_dashboard; ?>">
                <span id="mm-icon"><i class="ri-dashboard-2-line"></i></span>
                <span id="mm-text">Dashboard</span>
            </a>
        </li>
        <li class="list">
            <a href="<?php echo $base_url_dashboard; ?>/member.php">
                <span id="mm-icon"><i class="bx ri-user-3-line"></i></span>
                <span id="mm-text">Member</span>
            </a>
        </li>
        <li class="list">
            <a href="<?php echo $base_url_dashboard; ?>/manage-project.php">
                <span id="mm-icon"><i class="bx ri-clipboard-line"></i></span>
                <span id="mm-text">Project</span>
            </a>
        </li>
        <div id="mm-indicator"></div>
    </ul>
</div>
<!-- END OF MOBILE MENU SEECTION -->
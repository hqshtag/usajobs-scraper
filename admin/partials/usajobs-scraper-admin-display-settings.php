<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://wajihtagourty.ml/
 * @since      1.0.0
 *
 * @package    Usajobs_Scraper
 * @subpackage Usajobs_Scraper/admin/partials
 */


?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
    <?php settings_errors() ?>
    <h1 class="title" id="plugin-name">USAjobs Scraper</h1>
    <ul class="nav nav-tabs">
        <li class="active"><a href="#tab-1">Settings</a></li>
        <li><a href="#tab-2">About</a>
    </ul>
    <?php
    if (get_option('usajob_initial_jobs')) {
        echo '<div>
                <h4 id="ujs-update-timer"></h4>
              </div>';
    }
    ?>

    <div class="tab-content">
        <div id="tab-1" class="tab-pane active">
            <form method="POST" action="options.php">
                <?php
                settings_fields('usajobs-scraper-settings');
                do_settings_sections('usajobs-scraper-settings');
                echo '<p>In order to use the search API you will first need to obtain an API Key. To request an API Key, please go the the <a href="https://developer.usajobs.gov/APIRequest/Index">API Request</a> page and fill out an application.</p>';
                echo '<p>Click <a href="https://www.usajobs.gov/Help/working-in-government/pay-and-leave/work-schedules/">here</a> to learn more about these work schedules</p>';
                submit_button();
                ?>
            </form>
        </div>
        <div id="tab-2" class="tab-pane">
            <div class="about">
                <h1>About</h1>
                <p>This tool fetch job-ads from <a href="https://www.usajobs.gov/">usajobs.gov</a> and add to job listings. <br> This will require <b>WP job Mangaer</b> plugin already <em>installed</em> and <em>activated</em>.</p>
                <p>For more info contact me: <span id="myEmail">wajih.tagourty@gmail.com</span></p>
            </div>
        </div>
    </div>

</div>
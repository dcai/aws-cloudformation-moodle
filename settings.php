<?php
/*
 * This file is part of Spark LMS
 *
 * Copyright (C) 2010 onwards Spark Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Mustafa Bahcaci <mbahcaci@charterresources.us>
 * @package spark
 * @subpackage spark_admission
 */

// Application Menu.
$ADMIN->add('root', new admin_category('spark_admission', get_string('pluginname', 'spark_admission')));

$ADMIN->add('spark_admission', new admin_externalpage('sparkadmissionpluginsettings', get_string('pluginsettings', 'spark_admission'),
       $CFG->wwwroot.'/admin/settings.php?section=spark_admission_settings',
    array('moodle/site:config'))
);

$ADMIN->add('spark_admission', new admin_externalpage('application', get_string('applications', 'spark_admission'),
       $CFG->wwwroot.'/spark/admission/application.php',
    array('spark/admission:manageapplication', 'spark/admission:processapplication'))
);

$ADMIN->add('spark_admission', new admin_externalpage('application_form_fields', get_string('application_form_fields', 'spark_admission'),
       $CFG->wwwroot.'/spark/admission/form_fields.php', array('spark/admission:applicationsetting')));

// Settings.
$settings = new admin_settingpage('spark_admission_settings',  get_string('pluginname', 'spark_admission'));
$ADMIN->add('sparkplugins', $settings);


$settings->add(
    new admin_setting_configtext(
        'spark_admission/title',
        get_string('title', 'spark_admission'),
        '',
        'Application Form'
    )
);
$settings->add(
    new admin_setting_confightmleditor(
        'spark_admission/description',
        get_string('description', 'spark_admission'),
        '',
        ''
    )
);
$settings->add(
    new admin_setting_confightmleditor(
        'spark_admission/termsandconditions',
        get_string('termsandconditions', 'spark_admission'),
        '',
        '<p><strong>Terms and Conditions:</strong></p>
        <p style="text-align: justify;">I hereby certify that, to the best of my knowledge and belief, the answers to the foregoing 
        questions and statements made by me in this application are complete and accurate. I understand that any false information, 
        omissions, or misrepresentations of facts may result in rejection of this application or future dismissal of the applicant. 
        I also understand that the School will contact me for future communication.</p>'
    )
);
$settings->add(
    new admin_setting_confightmleditor(
        'spark_admission/footer',
        get_string('footer', 'spark_admission'),
        '',
        ''
    )
);

$settings->add(
    new admin_setting_heading(
        'spark_admission/verificationemail',
        get_string('verificationemail', 'spark_admission'),
        ''
    )
);

$settings->add(
    new admin_setting_configcheckbox(
        'spark_admission/sendverificationemail',
        get_string('sendverificationemail', 'spark_admission'),
        '',
        1
    )
);

$settings->add(
    new admin_setting_configtext(
        'spark_admission/verificationsubject',
        get_string('verificationsubject', 'spark_admission'),
        '',
        '[[schoolname]] email verification'
    )
);
$settings->add(
    new admin_setting_confightmleditor(
        'spark_admission/verificationbody',
        get_string('verificationbody', 'spark_admission'),
        '[[parentname]] [[appliedyear]] [[studentfirstname]] [[studentlastname]] [[validationurl]] [[today]]',
        '<p>Dear [[parentname]],</p>
        <p>We have received an online application for [[appliedyear]] school year at our website 
        for [[studentfirstname]] [[studentlastname]] on [[today]] Since future  communications, such as waiting list
         updates or other time sensitive enrollment information,  will be solely sent via this email we need to ensure that this email is valid.</p>
        <p>To validate your email and complete your application process click the link below.  If you do not respond timely to this email 
        your application will remain incomplete.  Incomplete applications may be denied admission.</p>
        <p><a target="_blank" href="[[validationurl]]"> Please click here to validate your email.</a></p>
        <p>Or copy and paste the link below to your browser\'s address bar and press enter.</p>
        <p>[[validationurl]]</p>
        <p>Please be aware that, this application does not guarantee the enrollment and serves as an intention to enroll, 
        and gives you a chance to be in the lottery if needed. You need to follow up the status of your application and finalize the 
        paperwork to complete the enrollment.</p>
        
        <p>Good Luck.</p>
        <p>Admission Office</p>'
    )
);
$settings->add(
    new admin_setting_configtext(
        'spark_admission/additionalrecipients',
        get_string('additionalrecipients', 'spark_admission'),
        get_string('commaseparatedemails', 'spark_admission'),
        ''
    )
);
$settings->add(
    new admin_setting_confightmleditor(
        'spark_admission/successmessage',
        get_string('successmessage', 'spark_admission'),
        '',
        '<p>Congratulations! Your application has been submitted successfully.</p>'
    )
);
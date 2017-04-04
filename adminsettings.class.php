<?php

require_once ($CFG->libdir . '/adminlib.php');

class admin_setting_configdate extends admin_setting {
    /** @var string Used for setting day */
    public $name2;

    /**
     * Constructor
     * @param string $monthconfigname setting for hours
     * @param string $dayconfigname setting for hours
     * @param string $visiblename localised
     * @param string $description long localised info
     */
    public function __construct($monthconfigname, $dayconfigname, $visiblename, $description) {
        $this->name2 = $dayconfigname;
        parent::__construct($monthconfigname, $visiblename, $description, null);
    }

    /**
     * Get the selected date
     *
     * @return mixed An array containing 'm'=>xx, 'd'=>xx, or null if not set
     */
    public function get_setting() {
        $month = $this->config_read($this->name);
        $day = $this->config_read($this->name2);
        if (is_null($month) or is_null($day)) {
            return NULL;
        }

        return array('m' => $month, 'd' => $day);
    }

    /**
     * Store the date
     *
     * @param array $data Must be form 'm'=>xx, 'd'=>xx
     * @return bool true if success, false if not
     */
    public function write_setting($data) {
        if (!is_array($data)) {
            return '';
        }

        $result = $this->config_write($this->name, (int)$data['m'])
            && $this->config_write($this->name2, (int)$data['d']);
        return ($result ? '' : get_string('errorsetting', 'admin'));
    }

    /**
     * Returns XHTML time select fields
     *
     * @param array $data Must be form 'h'=>xx, 'm'=>xx
     * @param string $query
     * @return string XHTML time select fields and wrapping div(s)
     */
    public function output_html($data, $query='') {
        $month = array(
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'May',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Aug',
            9 => 'Sep',
            10 => 'Oct',
            11 => 'Nov',
            12 => 'Dec',
        );
        $default = $this->get_defaultsetting();

        if (is_array($default)) {
            $defaultinfo = $default['m'].' '.$default['d'];
        } else {
            $defaultinfo = NULL;
        }

        $return  = '<div class="form-time defaultsnext">';
        $return .= '<label class="accesshide" for="' . $this->get_id() . 'm">' . get_string('month') . '</label>';
        $return .= '<select id="' . $this->get_id() . 'h" name="' . $this->get_full_name() . '[m]">';
        for ($i = 1; $i <= 12; $i++) {
            $return .= '<option value="' . $i . '"' . ($i == $data['m'] ? ' selected="selected"' : '') . '>' . $month[$i] . '</option>';
        }
        $return .= '</select>';
        $return .= '<label class="accesshide" for="' . $this->get_id() . 'd">' . get_string('day') . '</label>';
        $return .= '<select id="' . $this->get_id() . 'd" name="' . $this->get_full_name() . '[d]">';
        for ($i = 1; $i <= 31; $i ++) {
            $return .= '<option value="' . $i . '"' . ($i == $data['d'] ? ' selected="selected"' : '') . '>' . $i . '</option>';
        }
        $return .= '</select>';
        $return .= '</div>';
        return format_admin_setting($this, $this->visiblename, $return, $this->description,
            $this->get_id() . 'm', '', $defaultinfo, $query);
    }

}


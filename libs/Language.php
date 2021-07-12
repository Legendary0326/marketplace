<?php
class Language
{
    private $path_cache;
    private $language = 'en';

    function __construct()
    {
        if (!isset($_SESSION['Language'])) {
            $_SESSION['Language'] = $this->language;
        } else {
            $this->language = $_SESSION['Language'];
        }
        $this->path_cache = MAINPATH . 'cache/';
        if (defined('WATCHLANGUAGE') && WATCHLANGUAGE) $this->importLanguages();
    }

    public function translate($toTranslate, $file = null)
    {
        global $DB;
        $md5 = md5($toTranslate);
        $lang = $DB->getOne('translations', 'LangCode = ' . $DB->string($this->language) . ' AND Original = ' . $DB->string($md5));
        if (is_null($lang)) {
            $newData = [
                'LangCode'  => $this->language,
                'Original'  => $md5
            ];
            $DB->insert('translations', $newData);
            $DB->query('INSERT IGNORE INTO translations_originals (Original, Text) VALUES(' . $DB->string($md5) . ', ' . $DB->string($toTranslate) . ')');
            if (!empty($file)) $DB->query('INSERT IGNORE INTO translations_files (Original, File) VALUES(' . $DB->string($md5) . ', ' . $DB->string($file) . ')');
            return $toTranslate;
        } else {
            if (is_null($lang['Translated'])) {
                return $toTranslate;
            } else {
                return $lang['Translated'];
            }
        }
    }

    public function translateFile($file, $readonly = false)
    {
        global $Language_REALFILE;
        $Language_REALFILE = $file;
        $cache = $this->path_cache . $this->language . '_' . md5($file . '@' . filemtime($file)) . '.php';
        if (file_exists($cache)) {
            return $cache;
        } else {
            $content = @file_get_contents($file);
            $content = preg_replace_callback('/<lang>(.*)<\/lang>/isU', function ($a) use($file) {
                return $this->translate($a[1], $file);
            }, $content);
            if ($readonly) {
                return true;
            } else {
                if (preg_match('/^<\?php/', $content)) {
                    $content = preg_replace('/^<\?php/', '<?php /***REALFILE: ' . $file . '***/', $content);
                } else {
                    $content = '<?php /***REALFILE: ' . $file . '***/ ?>' . $content;
                }
                @file_put_contents($cache, $content);
                if (file_exists($cache)) return $cache;
            }
        }
        return $file;
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function setLanguage($language)
    {
        global $DB;
        if (!$DB->exists('languages', 'LangCode = ' . $DB->string($language))) return false;
        $this->language = $_SESSION['Language'] = $language;
    }

    public function getLanguages()
    {
        global $DB;
        $langs = $DB->get('languages', '', 'ORDER BY Sort');
        $all = [];
        if (is_array($langs) && count($langs) >= 1) {
            foreach ($langs as $lang) {
                $all[$lang['LangCode']] = $lang['Name'];
            }
            return $all;
        }
        return false;
    }

    public function getCountries()
    {
        global $DB;
        $countries = $DB->fetch_all($DB->query('SELECT CountryID, IFNULL(' . $DB->field($this->language) . ', en) AS Name FROM countries ORDER BY Name'), MYSQLI_ASSOC);
        $all = [];
        if (is_array($countries) && count($countries) >= 1) {
            foreach ($countries as $country) {
                $all[$country['CountryID']] = $country['Name'];
            }
            return $all;
        }
    }

    public function number($number, $decimals = 2)
    {
        return number_format($number, $decimals, '.', '');
    }

    public function date($timestamp)
    {
        return date('Y-m-d', $timestamp);
    }

    private function importLanguages()
    {
        global $DB;
        $Languages = $DB->get('languages');
        foreach ($Languages as $Language) {
            $Filename = MAINPATH . 'langs/' . $Language['LangCode'] . '.txt';
            if (file_exists($Filename) && filemtime($Filename) != $Language['LastSeen']) {
                $DB->delete('translations', 'LangCode = ' . $DB->string($Language['LangCode']));
                $files = glob('cache/' . $Language['LangCode'] . '_*.php');
                $DB->update('languages', ['LastSeen' => filemtime($Filename)], 'LangCode = ' . $DB->string($Language['LangCode']));
                foreach ($files as $file) unlink($file);
                preg_match_all("/'([^']+)'\s*=\s*'([^']+)'/U", file_get_contents($Filename), $Data);
                if (!isset($Data[0]) || count($Data[0]) == 0) continue;
                foreach ($Data[1] as $ID => &$Original) {
                    $Translation = &$Data[2][$ID];
                    $md5 = md5($Original);
                    $DB->query('INSERT IGNORE INTO translations_originals (Original, Text) VALUES(' . $DB->string($md5) . ', ' . $DB->string($Original) . ')');
                    $DB->insert('translations', ['LangCode' => $Language['LangCode'], 'Original' => $md5, 'Translated' => $Translation]);
                }
            }
        }
        return true;
    }

    public function getCommunicateLanguages()
    {
        return [
            'en'    =>  'English',
            'nl'    =>  'Nederlands',
            'fr'    =>  'Français',
            'es'    =>  'Español',
            'de'    =>  'Deutsch',
            'ru'    =>  'Русский',
            'it'    =>  'Italiano',
            'se'    =>  'Svenska',
            'fi'    =>  'Suomalainen',
            'no'    =>  'Norsk',
            'he'    =>  'עברית',
            'ar'    =>  'العربية',
            'hi'    =>  'हिंदी',
            'zh'    =>  '中文',
            'tr'    =>  'Türkçe',
            'pl'    =>  'Polski',
            'hu'    =>  'Magyar',
            'vi'    =>  'Tiếng Việt',
            'ja'    =>  '日本語',
            'ro'    =>  'Română',
            'cs'    =>  'Čeština',
            'uk'    =>  'Українською',
            'el'    =>  'Ελληνικά',
            'al'    =>  'Shqiptar'
        ];
    }
}
<?php

require_once('xml.php');

class locale
{
    function __construct($language, $country)
    {
        $this->setup($language, $country);
    }

    function setup($language, $country)
    {
        in_array($language, self::$languages) or runtime_error('Unknown language alpha2 code: ' . $language);
        in_array($country, self::$countries) or runtime_error('Unknown country alpha2 code: ' . $country);
        $this->language = $language;
        $this->country = $country;
    }

    function load($filename)
    {
        $this->xml = xml::load($filename);
        $query = array();
        foreach(self::$languages as $language)
        {
            $query[] = "/local//$language";
        }

        foreach($this->xml->query(implode('|', $query)) as $node)
        {
            $doc = new xml();
            foreach($this->xml->query('* | text()', $node) as $piece)
            {
                $doc->append($doc->import($piece));
            }
            $path = explode(':', str_replace('/', ':', trim($node->path(), '/')));
            array_shift($path);
            $this->local[implode(':', $path)] = $doc->get();
        }
    }

    function get($alias)
    {
        $alias .= ':' . $this->language;
        return isset($this->local[$alias]) ? $this->local[$alias] : "[Alias not found: $alias]";
    }

    private $language;
    private $country;
    private $xml;
    private $local = array();

    static private $languages = array
    (
        'aa', 'ab', 'ae', 'af', 'am', 'an', 'ar', 'as', 'ay', 'az',
        'ba', 'be', 'bg', 'bh', 'bi', 'bn', 'bo', 'br', 'bs',
        'ca', 'ce', 'ch', 'co', 'cs', 'cu', 'cv', 'cy',
        'da', 'de', 'dv', 'dz',
        'el', 'en', 'eo', 'es', 'et', 'eu',
        'fa', 'fi', 'fj', 'fo', 'fr', 'fy',
        'ga', 'gd', 'gl', 'gn', 'gu', 'gv',
        'ha', 'he', 'hi', 'ho', 'hr', 'ht', 'hu', 'hy', 'hz',
        'ia', 'id', 'ie', 'ii', 'ik', 'io', 'is', 'it', 'iu',
        'ja', 'jv',
        'ka', 'ki', 'kj', 'kk', 'kl', 'km', 'kn', 'ko', 'ks', 'ku', 'kv', 'kw', 'ky',
        'la', 'lb', 'li', 'ln', 'lo', 'lt', 'lv',
        'mg', 'mh', 'mi', 'mk', 'ml', 'mn', 'mo', 'mr', 'ms', 'mt', 'my',
        'na', 'nb', 'nd', 'ne', 'ng', 'nl', 'nn', 'no', 'nr', 'nv', 'ny',
        'oc', 'om', 'or', 'os',
        'pa', 'pi', 'pl', 'ps', 'pt',
        'qu',
        'rm', 'rn', 'ro', 'ru', 'rw',
        'sa', 'sc', 'sd', 'se', 'sg', 'si', 'sk', 'sl', 'sm', 'sn', 'so', 'sq', 'sr', 'ss', 'st', 'su', 'sv', 'sw',
        'ta', 'te', 'tg', 'th', 'ti', 'tk', 'tl', 'tn', 'to', 'tr', 'ts', 'tt', 'tw', 'ty',
        'ug', 'uk', 'ur', 'uz',
        'vi', 'vo',
        'wa', 'wo',
        'xh',
        'yi', 'yo',
        'za', 'zh', 'zu'
    );

    static private $countries = array
    (
        'ad', 'ae', 'af', 'ag', 'ai', 'al', 'am', 'ao', 'aq', 'ar', 'as', 'at', 'au', 'aw', 'ax', 'az',
        'ba', 'bb', 'bd', 'be', 'bf', 'bg', 'bh', 'bi', 'bj', 'bl', 'bm', 'bn', 'bo', 'bq', 'br', 'bs', 'bt', 'bv', 'bw', 'by', 'bz',
        'ca', 'cc', 'cd', 'cf', 'cg', 'ch', 'ci', 'ck', 'cl', 'cm', 'cn', 'co', 'cr', 'cu', 'cv', 'cw', 'cx', 'cy', 'cz',
        'de', 'dj', 'dk', 'dm', 'do', 'dz',
        'ec', 'ee', 'eg', 'eh', 'er', 'es', 'et',
        'fi', 'fj', 'fk', 'fm', 'fo', 'fr',
        'ga', 'gb', 'gd', 'ge', 'gf', 'gg', 'gh', 'gi', 'gl', 'gm', 'gn', 'gp', 'gq', 'gr', 'gs', 'gt', 'gu', 'gw', 'gy',
        'hk', 'hm', 'hn', 'hr', 'ht', 'hu',
        'id', 'ie', 'il', 'im', 'in', 'io', 'iq', 'ir', 'is', 'it',
        'je', 'jm', 'jo', 'jp',
        'ke', 'kg', 'kh', 'ki', 'km', 'kn', 'kp', 'kr', 'kw', 'ky', 'kz',
        'la', 'lb', 'lc', 'li', 'lk', 'lr', 'ls', 'lt', 'lu', 'lv', 'ly',
        'ma', 'mc', 'md', 'me', 'mf', 'mg', 'mh', 'mk', 'ml', 'mm', 'mn', 'mo', 'mp', 'mq', 'mr', 'ms', 'mt', 'mu', 'mv', 'mw', 'mx', 'my', 'mz',
        'na', 'nc', 'ne', 'nf', 'ng', 'ni', 'nl', 'no', 'np', 'nr', 'nu', 'nz',
        'om',
        'pa', 'pe', 'pf', 'pg', 'ph', 'pk', 'pl', 'pm', 'pn', 'pr', 'ps', 'pt', 'pw', 'py',
        'qa',
        're', 'ro', 'rs', 'ru', 'rw',
        'sa', 'sb', 'sc', 'sd', 'se', 'sg', 'sh', 'si', 'sj', 'sk', 'sl', 'sm', 'sn', 'so', 'sr', 'ss', 'st', 'sv', 'sx', 'sy', 'sz',
        'tc', 'td', 'tf', 'tg', 'th', 'tj', 'tk', 'tl', 'tm', 'tn', 'to', 'tr', 'tt', 'tv', 'tw', 'tz',
        'ua', 'ug', 'um', 'us', 'uy', 'uz',
        'va', 'vc', 've', 'vg', 'vi', 'vn', 'vu',
        'wf', 'ws',
        'ye', 'yt',
        'za', 'zm', 'zw'
    );
}

?>
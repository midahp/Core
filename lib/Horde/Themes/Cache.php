<?php
/**
 * This class is responsible for parsing/building theme elements and then
 * caching these results.
 *
 * Copyright 2010 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @author   Michael Slusarz <slusarz@horde.org>
 * @category Horde
 * @license  http://www.fsf.org/copyleft/lgpl.html LGPL
 * @package  Core
 */
class Horde_Themes_Cache implements Serializable
{
    /* Constants */
    const HORDE_DEFAULT = 1;
    const APP_DEFAULT = 2;
    const HORDE_THEME = 4;
    const APP_THEME = 8;

    /**
     * Has the data changed?
     *
     * @var boolean
     */
    public $changed = false;

    /**
     * Application name.
     *
     * @var string
     */
    protected $_app;

    /**
     * Theme data.
     *
     * @var array
     */
    protected $_data = array();

    /**
     * Theme name.
     *
     * @var string
     */
    protected $_theme;

    /**
     * Constructor.
     *
     * @param string $app    The application name.
     * @param string $theme  The theme name.
     */
    public function __construct($app, $theme)
    {
        $this->_app = $app;
        $this->_theme = $theme;
    }

    /**
     * Build the entire theme data structure.
     *
     * @throws UnexpectedValueException
     */
    public function build()
    {
        $this->_data = array();

        $this->_build('horde', 'default', self::HORDE_DEFAULT);
        $this->_build('horde', $this->_theme, self::HORDE_THEME);
        if ($this->_app != 'horde') {
            $this->_build($this->_app, 'default', self::APP_DEFAULT);
            $this->_build($this->_app, $this->_theme, self::APP_THEME);
        }

        $this->changed = true;
    }

    /**
     * Add theme data from an app/theme combo.
     *
     * @param string $app    The application name.
     * @param string $theme  The theme name.
     * @param integer $mask  Mask for the app/theme combo.
     *
     * @throws UnexpectedValueException
     */
    protected function _build($app, $theme, $mask)
    {
        $path = $GLOBALS['registry']->get('themesfs', $app) . '/'. $theme;
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));

        foreach ($it as $val) {
            if (!$val->isDir()) {
                $sub = $it->getSubPathname();

                if (isset($this->_data[$sub])) {
                    $this->_data[$sub] |= $mask;
                } else {
                    $this->_data[$sub] = $mask;
                }
            }
        }
    }

    /**
     */
    public function get($item, $mask = 0)
    {
        if (!($entry = $this->_get($item))) {
            return null;
        }

        if ($mask) {
            $entry &= $mask;
        }

        if ($entry & self::APP_THEME) {
            $app = $this->_app;
            $theme = $this->_theme;
        } elseif ($entry & self::HORDE_THEME) {
            $app = 'horde';
            $theme = $this->_theme;
        } elseif ($entry & self::APP_DEFAULT) {
            $app = $this->_app;
            $theme = 'default';
        } else {
            $app = 'horde';
            $theme = 'default';
        }

        return $this->_getOutput($app, $theme, $item);
    }

    /**
     */
    protected function _get($item)
    {
        if (!isset($this->_data[$item])) {
            $entry = 0;

            $path = $GLOBALS['registry']->get('themesfs', 'horde');
            if (file_exists($path . '/default/' . $item)) {
                $entry |= self::HORDE_DEFAULT;
            }
            if (file_exists($path . '/' . $this->_theme . '/' . $item)) {
                $entry |= self::HORDE_THEME;
            }

            if ($this->_app != 'horde') {
                $path = $GLOBALS['registry']->get('themesfs', $this->_app);
                if (file_exists($path . '/default/' . $item)) {
                    $entry |= self::APP_DEFAULT;
                }
                if (file_exists($path . '/' . $this->_theme . '/' . $item)) {
                    $entry |= self::APP_THEME;
                }
            }

            $this->_data[$item] = $entry;
            $this->changed = true;
        }

        return $this->_data[$item];
    }

    /**
     */
    protected function _getOutput($app, $theme, $item)
    {
        return array(
            'fs' => $GLOBALS['registry']->get('themesfs', $app) . '/' . $theme . '/' . $item,
            'uri' => $GLOBALS['registry']->get('themesuri', $app) . '/' . $theme . '/' . $item
        );
    }

    /**
     */
    public function getAll($item, $mask = 0)
    {
        if (!($entry = $this->_get($item))) {
            return array();
        }

        if ($mask) {
            $entry &= $mask;
        }
        $out = array();

        if ($entry & self::APP_THEME) {
            $out[] = $this->_getOutput($this->_app, $this->_theme, $item);
        }
        if ($entry & self::HORDE_THEME) {
            $out[] = $this->_getOutput('horde', $this->_theme, $item);
        }
        if ($entry & self::APP_DEFAULT) {
            $out[] = $this->_getOutput($this->_app, 'default', $item);
        }
        if ($entry & self::HORDE_DEFAULT) {
            $out[] = $this->_getOutput('horde', 'default', $item);
        }

        return $out;
    }

    /**
     */
    protected function _getExpireId()
    {
        switch ($GLOBALS['config']['themescacheparams']['check']) {
        case 'appversion':
        default:
            $id = array($GLOBALS['registry']->getVersion($this->_app));
            if ($this->_app != 'horde') {
                $id[] = $GLOBALS['registry']->getVersion('horde');
            }
            return 'v:' . implode('|', $id);

        case 'none':
            return '';
        }
    }

    /* Serializable methods. */

    /**
     */
    public function serialize()
    {
        return serialize(array(
            $this->_getExpireId(),
            $this->_app,
            $this->_data,
            $this->_theme
        ));
    }

    /**
     */
    public function unserialize($data)
    {
        list(
            $expire_id,
            $this->_app,
            $this->_data,
            $this->_theme
        ) = unserialize($data);

        if ($expire_id && ($expire_id != $this->_getExpireId())) {
            throw new Exception('Cache invalidated');
        }
    }

}

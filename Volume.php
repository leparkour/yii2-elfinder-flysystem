<?php
/**
 * Date: 03.07.2024
 * Time: 22:40
 */
namespace mihaildev\elfinder\flysystem;


use League\Flysystem\Filesystem;
use mihaildev\elfinder\volume\Base;
use yii\base\InvalidConfigException;
use League\Flysystem\PhpseclibV3\SftpAdapter;
use League\Flysystem\PhpseclibV3\SftpConnectionProvider;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;


class Volume extends Base
{
    public $driver = 'Flysystem';

    public $separator = "/";
    public $path = "/";
    public $url;

    public $component;

    public $glideURL;
    public $glideKey;

    protected function optionsModifier($options)
    {
        if (empty($this->component)) {
            throw new InvalidConfigException('The "component" property must be set.');
        }

        /** @var DiecodingFilesystem $component */

        if (is_string($this->component)) {
            $component = \Yii::$app->get($this->component);
        } else {
            $component = \Yii::createObject($this->component);
        }

        if (!($component instanceof \diecoding\flysystem\SftpComponent || $component instanceof DiecodingFilesystem)) {
            throw new InvalidConfigException('A Filesystem instance is required');
        }

        $options['separator'] = $this->separator;
        $options['filesystem'] = new Filesystem(new SftpAdapter(
            new SftpConnectionProvider($this->component['config']),
            $this->component['root'],
            PortableVisibilityConverter::fromArray($this->component['perm'])
        ));;

        $options['path'] = $this->path;

        if (!empty($this->glideURL) && !empty($this->glideKey)) {
            $options['glideURL'] = $this->glideURL;
            $options['glideKey'] = $this->glideKey;
            unset($options['tmbPath']);
            unset($options['tmbURL']);
        }

        if (!empty($this->url)) {
            $options['URL'] = $this->url;
        }

        return $options;
    }
}

<?php

class Fidesio_Preorder_Block_Html_Head extends Mage_Page_Block_Html_Head
{
    protected function &_prepareStaticAndSkinElements($format, array $staticItems, array $skinItems,
                                                      $mergeCallback = null)
    {
        $designPackage = Mage::getDesign();
        $baseJsUrl = Mage::getBaseUrl('js');
        $items = array();
        if ($mergeCallback && !is_callable($mergeCallback)) {
            $mergeCallback = null;
        }

        // get static files from the js folder, no need in lookups
        foreach ($staticItems as $params => $rows) {
            foreach ($rows as $name) {
                $items[$params][] = $mergeCallback ? Mage::getBaseDir() . DS . 'js' . DS . $name : $baseJsUrl . $name;
            }
        }


        // lookup each file basing on current theme configuration
        foreach ($skinItems as $params => $rows) {

            if (strpos($format, 'script') != false) {
                $hash = '';
                foreach ($rows as $name) {
                    $row_path = $designPackage->getFilename($name, array('_type' => 'skin'));
                    if (is_readable($row_path)) {
                        $hash = sha1($hash.'|'.sha1_file($row_path));
                    }
                }
                $bundle_name = 'fidesio_preorder'.DS.'bundle'.DS.sha1($hash).'-'.crc32($hash) .'.js';
                $bundle_path = Mage::getBaseDir().DS.'media'.DS.$bundle_name;

                if (!is_readable($bundle_path)) {
                    $content = '(function () {';
                    foreach ($rows as $name) {
                        $row_path = $designPackage->getFilename($name, array('_type' => 'skin'));
                        if (is_readable($row_path)) {
                            $content .= '// '.$row_path.PHP_EOL;
                            $content .= file_get_contents($row_path);
                            $content .= PHP_EOL.PHP_EOL;
                        }
                    }
                    $content .='})();';


                    $bundle_dir = dirname($bundle_path);

                    if (!is_readable($bundle_dir)) {
                        mkdir($bundle_dir, 0777, true);
                    }
                    file_put_contents($bundle_path, $content);
                }

                $items[$params][] = Mage::getBaseUrl('media').$bundle_name;

            } else {
                foreach ($rows as $name) {
                    $items[$params][] = $mergeCallback ? $designPackage->getFilename($name, array('_type' => 'skin'))
                        : $designPackage->getSkinUrl($name, array());
                }
            }

        }

        $html = '';
        foreach ($items as $params => $rows) {
            // attempt to merge
            $mergedUrl = false;
            if ($mergeCallback) {
                $mergedUrl = call_user_func($mergeCallback, $rows);
            }
            // render elements
            $params = trim($params);
            $params = $params ? ' ' . $params : '';
            if ($mergedUrl) {
                $html .= sprintf($format, $mergedUrl, $params);
            } else {
                foreach ($rows as $src) {
                    $html .= sprintf($format, $src, $params);
                }
            }
        }
        return $html;
    }
}

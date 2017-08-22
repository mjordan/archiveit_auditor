#!/usr/bin/env php
<?php

/**
 * @file
 * Script to fetch the Archive-It CDX/C record for a given URL, then fetch the content
 * of the request to the URL, then validate its SHA-1 checksum.
 *
 * Input is a file of URLs preserved in Archive-It.
 *
 * @todo:
 * -Add Guzzle error handling.
 * -Log results.
 * -Get the Archive-It collection ID that the URL exists in.
 * -Sort CDX/C record rows so we can use the most recent for our checksum validation.
 */

require_once 'vendor/autoload.php';

use Base32\Base32;
use Monolog\Logger;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

define('CDXENDPOINT', 'http://wayback.archive-it.org/all/timemap/cdx?url=');
define('WAYBACKENDPOINT', 'http://wayback.archive-it.org/');

$options = getopt('c:u:');

$collection_id = $options['c'];
$path_to_url_list = $options['u'];
$urls = file($path_to_url_list);

foreach ($urls as $url) {
    $cdx_entries = array();
    $cdx_request = CDXENDPOINT . trim($url);
    $client = new GuzzleHttp\Client();
    $res = $client->request('GET', $cdx_request);
    $cdx_record = $res->getBody();
    $cdx_rows = preg_split("/\\r\\n|\\r|\\n/", $cdx_record);
    foreach ($cdx_rows as $key => $entry) {
        if (strlen($entry)) {
            $entry_record = explode(' ', $entry);
            // We only want entries resulting from an HTTP 200 response.
            if ($entry_record[4] == '200') {
                $cdx_entries[] = $entry_record;
            }
        }
    }

    // @todo: In production, there may be more than one 200 record, so we'll need to sort by
    // timestamp and take the most recent.
    $cdx = $cdx_entries[0];

    // @todo: '7100' here is the collection ID. In production, we'll need to get that
    // from somewhere - maybe as a parameter to this script?
    $file_to_download = WAYBACKENDPOINT . $collection_id . '/' . $cdx[1] . 'id_/' . $cdx[2];
    $client = new GuzzleHttp\Client();
    $res = $client->request('GET', $file_to_download);
    file_put_contents('temp.htm', $res->getBody());

    // Get the raw binary digest of the retrieved file.
    $sha1 = sha1_file('temp.htm', TRUE);

    // Archive-It stores its sha1 digests in Base32, so we need to convert.
    $encoded = Base32::encode($sha1);
    if ($encoded == $entry_record[5]) {
        print "CDX/C checksum for {$cdx[2]} validated.\n";
    }
    else {
        print "WARNING: CDX/C checksum {$cdx[2]} invalid.\n";
    }

    unlink('temp.htm');
}

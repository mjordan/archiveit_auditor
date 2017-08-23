# Archive-It Auditor

Script to verify SHA-1 checksums of files in Archive-It collections.

## Requirements

* PHP 5.5.0 or higher.
* [Composer](https://getcomposer.org)

## Installation

1. `git clone https://github.com/mjordan/archiveit_auditor`
1. `cd archiveit_auditor`
1. `php composer.phar install` (or equivalent on your system, e.g., `./composer install`)

## Overview and usage

The input for this script is a list of URLs that are known to be archived in Archive-It, one URL per line. Once you have the list of URLs, run the script with its two required parameters, `-c` specifying the Archive-It collection ID, and `-u` specifying the path to the file containing the URLs to audit: 

`./audit -c 7100 -u urls.txt`

The script will iterate through the list of URLs, use the Archive-It [CDX/C API](https://support.archive-it.org/hc/en-us/articles/115001790023-Access-Archive-It-s-Wayback-index-with-the-CDX-C-API) to retrieve information about the URL, fetch the content associated with the URL from Archive-It, and validate the content's SHA-1 checksum. Results of the validation are written to a log file specified in the optional `-l` parameter, which defaults to 'archiveit_audit.log'. Errors are also logged to this file.

## To do

This script is currently under development. Some outstanding tasks include:

* Figure out how to generate a list of URLs to test (e.g., via an Archive-It API, from downloaded crawl reports, etc.).

## Maintainer

* [Mark Jordan](https://github.com/mjordan)

## Development and feedback

Bug reports, use cases and suggestions are welcome. If you want to open a pull request, please open an issue first.

## License

The Unlicense

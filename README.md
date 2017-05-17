# commons-category-downloader

A simple command line PHP script for downloading all files and page titles in a category.

This traverses a given category and all of its descendent categories, and does two things:

1. Saves the page name (i.e. or file name, for the File namespace) to `pagenames.txt`; and
2. Downloads the file to `files/`.

It checks the files' checksums before downloading,
so it can be interrupted and restarted at any time and it won't re-download anything.

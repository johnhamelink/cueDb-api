CueDB API
=========

What is CueDB?
--------------

CueDB is a database which stores Cue files that are generated with flac music files.
Sometimes, you'll come into the situation where you accidently lose your cuesheet,
yet you have your flac file. Instead of having to re-rip your flac file, you can
query this database to see if someone else has uploaded a cuesheet that matches the
album you have a rip of.

This repository holds the source of the API, which stores and retrieves the cuesheets
using gridFS.

API Reference
-------------

The CueDB API requires a few different things in order to add an item to the DB. It 
needs:

* The name of the artist
* The name of the album
* The MD5 sum of the contents of the flac file
* The sha1 sum of the contents of the flac file
* The sha256 sum of the contents of the flac file
* The crc32 sum of the contents of the flac file
* The cuesheet file

The cueDB add route is ``<baseurl>/add``

![A screenshot of the parameters](http://i.imgur.com/HAoLl.png)

The CueDB API returns JSON responses.

---

The CueDB API can be queried in a variety of ways to find cuesheets:

* By the MD5 sum of the contents of the flac file
* By the sha1 sum of the contents of the flac file
* By the sha256 sum of the contents of the flac file
* By the crc32 sum of the contents of the flac file

The CueDB query route is ``<baseurl>/query/<signature type>/<signature>``

The CueDB API returns JSON responses.
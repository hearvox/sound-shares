<!DOCTYPE html>
<html >
<head>
    <meta charset="UTF-8">
    <title>Sound Shares: Audio</title>
    <link rel="stylesheet" href="css/sound-shares-player.css">
</head>

<body>

<div id="container">

    <div id="player">

        <?php $file   = ( ! empty( $_GET['file'] ) ) ? $_GET['file'] : '' ; ?>
        <audio id="song" class="song">
          <source src="<?php echo filter_var( $file, FILTER_SANITIZE_URL ); ?>" type="audio/mp3" />
        </audio>

        <?php if ( ! empty( $_GET['title'] ) || ! empty( $_GET['author'] ) ) { ?>
        <?php $title    = ( ! empty( $_GET['title'] ) ) ? $_GET['title'] : 'Audio'; ?>
        <?php $author   = ( ! empty( $_GET['author'] ) ) ? 'by ' . $_GET['author'] : ''; ?>
        <?php
        if ( empty( $_GET['file'] ) ) {
          $title = '<em>[NO AUDIOFILE]</em>';
        }
        ?>
        <div id="info">
          <h1><?php echo filter_var( $title, FILTER_SANITIZE_STRING ); ?></h1>
          <h2><?php echo filter_var( $author, FILTER_SANITIZE_STRING ); ?></h2>
        </div>
        <?php } ?>

        <div id="controls">
          <div class="group" id="r1">
            <a class="big" id="play" href="" title="">
              <i class="icon-play"></i>
            </a>
          </div>

          <div class="group" id="time">
            <div class="static">
              <label></label>
              <span id="currentTime">00:00</span>
              <span id="totalTime">00:00</span>
            </div>
          </div>

          <div class="group" id="seekHolder">
            <div id="timeLabel">00:00</div>
            <div class="static rangeContainer">
              <label></label>
              <div class="rangeHorizontal stripes" id="seek"></div>
              <div id="seekFill" class="stripes"></div>
              <div id="seekDrag"></div>
            </div>
          </div>

          <div class="group" id="right">
            <a id="mute" href="" title="">
              <label class="expandable">VOL</label>
              <i class="icon-volume-up" id="volumeIcon"></i>
            </a>
            <a id="loop" href="" title="">
              <label class="expandable">LOOP</label>
              <i class="icon-ban-circle" id="loopIcon"></i>
            </a>
          </div>
        </div>

    </div>

</div>

<script src="js/sound-shares-player.js"></script>

<script type="text/javascript">

    // window.onload = audio_load();

    function audio_load() {
        audio = document.getElementsByTagName('audio');
        console.log( audio[0].readyState );
    }

</script>

<!--
http://rji.local/wp-content/plugins/sound-shares/player.php?file=http%3A%2F%2Fhearingvoices.com%2Fnews%2Fwp-content%2Fuploads%2F2012%2F09%2F1Solidod_Billboard.mp3&title=HV142+Solidod+%28promo%29&author=Hearing+Voices

$audiodata = array( 'http://hearingvoices.com/news/wp-content/uploads/2012/09/1Solidod_Billboard.mp3', 'HV142 Solidod (promo)', 'Hearing Voices', 'http://hearingvoices.com/2012/09/hv142-solidod/' );
 -->

<!--
Audio Player:
Copyright (c) 2017 by Dave Pagurek (http://codepen.io/davepvm/pen/DgwlJ)

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
-->
</body>
</html>

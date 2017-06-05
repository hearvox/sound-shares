<?php

$audiodata = array( 'http://hearingvoices.com/news/wp-content/uploads/2012/09/1Solidod_Billboard.mp3', '<small>HV142</small> Solidod (promo)', 'Hearing Voices', 'http://hearingvoices.com/2012/09/hv142-solidod/' );

?>
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

    <audio id="song">
      <source src="<?php echo $audiodata[0]; ?>" type="audio/mp3" />
    </audio>

    <div id="info">
      <h1><?php echo $audiodata[1]; ?></h1>
      <h2>by <a href="echo $audiodata[3];"><?php echo $audiodata[2]; ?></a></h2>
    </div>

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

</body>
</html>

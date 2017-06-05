var AudioModel = function (_song) {
  var isPlaying = false;
  var song = _song;

  var a = {};

  a.getSong = function() {
    return song;
  };

  a.currentTime = function() {
    return song.currentTime;
  };

  a.duration = function() {
    return song.duration;
  };

  a.setTime = function(time) {
    song.currentTime = time;
  };

  a.play = function() {
    isPlaying = true;
    song.play();
  };

  a.pause = function() {
    isPlaying = false;
    song.pause();
  };

  a.playing = function() {
    return isPlaying;
  };

  a.getLoop = function() {
    return song.loop;
  };

  a.setLoop = function(loop) {
    song.loop = loop;
  };

  a.setVolume = function(vol) {
    song.volume = vol;
  };

  a.getVolume = function() {
    return song.volume;
  };

  a.seekTo = function(time) {
    song.currentTime = time;
  };

  var reset = function() {
    if (!song.loop) {
      a.seekTo(0);
      a.pause();
    }
  };

  song.addEventListener("ended", function(event) { reset() }, false);

  return a;
}(document.getElementById("song"));

var AudioView = function(model) {
  var playBtn = document.getElementById("play");
  var currentTime = document.getElementById("currentTime");
  var totalTime = document.getElementById("totalTime");
  var seekBg = document.getElementById("seek");
  var seekHolder = document.getElementById("seekHolder");
  var seekFill = document.getElementById("seekFill");
  var seekDrag = document.getElementById("seekDrag");
  var volumeIcon = document.getElementById("volumeIcon");
  var loopIcon = document.getElementById("loopIcon");
  var timeLabel = document.getElementById("timeLabel");

  var formatTime = function(i) {
    var minutes = Math.floor(i/60);
    var seconds = Math.floor(i%60);
    return ((minutes < 10) ? ("0" + minutes) : minutes) + ":" + ((seconds < 10) ? ("0" + seconds) : seconds);
  };

  var play = function() {
    playBtn.innerHTML = "<i class='icon-pause'></i>";
  };

  var pause = function() {
    pauseBtn.innerHTML = "<i class='icon-play'></i>";
  };

  var updateTime = function() {
    currentTime.innerHTML = formatTime(model.currentTime());
    timeLabel.innerHTML = currentTime.innerHTML;
    if (Math.round((1-(model.currentTime()/model.duration()))*(seekBg.offsetWidth))+3<timeLabel.offsetWidth/2) {
      timeLabel.style.left = (seekHolder.offsetWidth-timeLabel.offsetWidth) + "px";
    } else if (Math.round(((model.currentTime()/model.duration()))*(seekBg.offsetWidth)+3)<timeLabel.offsetWidth/2) {
      timeLabel.style.left="0px";
    } else {
      timeLabel.style.left = Math.round(((model.currentTime()/model.duration()))*(seekBg.offsetWidth)+3-timeLabel.offsetWidth/2) + "px";
    }
    seekFill.style.width = Math.round((model.currentTime()/model.duration())*(seekBg.offsetWidth-2))+"px";
    seekDrag.style.left = Math.round((model.currentTime()/model.duration())*(seekBg.offsetWidth-2))+"px";
  };

  var updateVolume = function() {
    if (model.getVolume() === 0) {
      volumeIcon.className = "icon-volume-off";
    } else if (Math.round(model.getVolume()) === 0) {
      volumeIcon.className = "icon-volume-down";
    } else {
      volumeIcon.className = "icon-volume-up";
    }
  };

  var a = {};

  var timeProxy = function(event) {
    updateTime(this);
  }

  a.updateLoop = function() {
    if (model.getLoop()) {
      loopIcon.className = "icon-refresh icon-flip-horizontal";
    } else {
      loopIcon.className = "icon-ban-circle";
    }
  };

  a.listen = function() {
    model.getSong().addEventListener("timeupdate", timeProxy, false);
  };

  a.stopListening = function() {
    model.getSong().removeEventListener("timeupdate", timeProxy, false);
  }

  a.dragSeek = function(mousePos) {
    var dragTime = (mousePos.x/seekBg.offsetWidth)*model.duration();
    timeLabel.innerHTML = formatTime(dragTime);
    if (Math.round((1-(dragTime/model.duration()))*(seekBg.offsetWidth))+3<timeLabel.offsetWidth/2) {
      timeLabel.style.left = (seekHolder.offsetWidth-timeLabel.offsetWidth) + "px";
    } else if (Math.round(((dragTime/model.duration()))*(seekBg.offsetWidth)+3)<timeLabel.offsetWidth/2) {
      timeLabel.style.left="0px";
    } else {
      timeLabel.style.left = Math.round(((dragTime/model.duration()))*(seekBg.offsetWidth)+3-timeLabel.offsetWidth/2) + "px";
    }
    seekFill.style.width = Math.round((dragTime/model.duration())*(seekBg.offsetWidth-2))+"px";
    seekDrag.style.left = Math.round((dragTime/model.duration())*(seekBg.offsetWidth-2))+"px";
  };

  var init = function() {
    totalTime.innerHTML = formatTime(song.duration);

    if (this.autoPlay) {
      this.play();
    }
    model.getSong().addEventListener("play", function(event) { play(); }, false);
    model.getSong().addEventListener("pause", function(event) { pause(); }, false);
    model.getSong().addEventListener("ended", function(event) { if (!model.loop()) pause(); }, false);
    model.getSong().addEventListener("volumechange", function(event) { updateVolume(); }, false);
    a.listen();
  };

  model.getSong().addEventListener("canplaythrough", function(event) { init(); }, false);

  return a;
}(AudioModel);

var AudioControl = function(model, view) {
  var playBtn = document.getElementById("play");
  var seekBg = document.getElementById("seek");
  var seekHolder = document.getElementById("seekHolder");
  var seekFill = document.getElementById("seekFill");
  var seekDrag = document.getElementById("seekDrag");
  var muteBtn = document.getElementById("mute");
  var volumeIcon = document.getElementById("volumeIcon");
  var loopBtn = document.getElementById("loop");
  var loopIcon = document.getElementById("loopIcon");
  var timeLabel = document.getElementById("timeLabel");

  var togglePlay = function(event) {
    event.preventDefault();
    if (model.playing()) {
      model.pause();
    } else {
      model.play();
    }
  };

  var toggleLoop = function(event) {
    event.preventDefault();
    model.setLoop(!model.getLoop());
    view.updateLoop();
  };

  var toggleVolume = function(event) {
    event.preventDefault();
    if (model.getVolume()==1) {
      model.setVolume(0.49);
    } else if (model.getVolume()==0.49) {
      model.setVolume(0);
    } else {
      model.setVolume(1);
    }
  };

  var getMousePos = function(evt, element) {
    var rect = element.getBoundingClientRect();
    var root = document.documentElement;

    var mouseX = evt.clientX - rect.left - root.scrollLeft;
    var mouseY = evt.clientY - rect.top - root.scrollTop;

    return {x:mouseX, y:mouseY};
  };

  var startSeek = function(event) {
    event.preventDefault();
    view.stopListening();
    document.addEventListener("mousemove", seekProxy, false);
    document.addEventListener("mouseup", endSeekProxy, false);
  };

  var seekProxy = function(event) {
    seek(event);
  };

  var endSeekProxy = function(event) {
    endSeek(event);
  };

  var seek = function(event) {
    event.preventDefault();
    view.dragSeek(getMousePos(event, seekBg));
  };

  var endSeek = function(event) {
    event.preventDefault();
    view.listen();
    document.removeEventListener("mousemove", seekProxy, false);
    document.removeEventListener("mouseup", endSeekProxy, false);
    var mousePos = getMousePos(event, seekBg);
    model.seekTo((mousePos.x/seekBg.offsetWidth)*model.duration());
  };

  playBtn.addEventListener("click", function(event) { togglePlay(event); }, false);
  loopBtn.addEventListener("click", function(event) { toggleLoop(event); }, false);
  muteBtn.addEventListener("click", function(event) { toggleVolume(event); }, false);
  seekBg.addEventListener("mousedown", function(event) { startSeek(event); }, false);
  seekFill.addEventListener("mousedown", function(event) { startSeek(event); }, false);
  seekDrag.addEventListener("mousedown", function(event) { startSeek(event); }, false);

  return {};
}(AudioModel, AudioView);

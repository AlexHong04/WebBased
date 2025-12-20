(function () {
  var slidesEl = document.getElementById("slides");
  if (!slidesEl) return;
  var slides = slidesEl.children;
  var total = slides.length;
  var index = 0;
  var interval = 7000; // ms
  var timer = null;

  function go(to) {
    if (to < 0) to = total - 1;
    if (to >= total) to = 0;
    index = to;
    slidesEl.style.transform = "translateX(" + -index * 100 + "%)";
    updateDots();
  }

  function updateDots() {
    var dots = document.querySelectorAll("#dots .slider-dot");
    dots.forEach(function (d) {
      d.classList.add("inactive");
    });
    var active = dots[index];
    if (active) active.classList.remove("inactive");
  }

  function next() {
    go(index + 1);
  }

  function prev() {
    go(index - 1);
  }

  document.getElementById("next").addEventListener("click", function (e) {
    e.preventDefault();
    next();
    restart();
  });
  document.getElementById("prev").addEventListener("click", function (e) {
    e.preventDefault();
    prev();
    restart();
  });

  document.querySelectorAll("#dots .slider-dot").forEach(function (btn) {
    btn.addEventListener("click", function () {
      var i = parseInt(this.dataset.index, 10);
      go(i);
      restart();
    });
  });

  function start() {
    timer = setInterval(next, interval);
  }

  function stop() {
    if (timer) {
      clearInterval(timer);
      timer = null;
    }
  }

  function restart() {
    stop();
    start();
  }

  // pause on hover
  var slider = document.querySelector(".slider");
  slider.addEventListener("mouseenter", stop);
  slider.addEventListener("mouseleave", start);

  // keyboard navigation
  document.addEventListener("keydown", function (e) {
    if (e.key === "ArrowLeft") prev();
    if (e.key === "ArrowRight") next(); 
  });

  // init
  go(0);
  start();
})();



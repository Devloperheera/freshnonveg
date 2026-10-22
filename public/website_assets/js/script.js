$(function () {
    var io = new IntersectionObserver(
        function (es) {
            es.forEach(function (e, i) {
                if (e.isIntersecting) {
                    setTimeout(function () {
                        e.target.classList.add('in');
                    }, (i % 4) * 120);
                    io.unobserve(e.target);
                }
            });
        },
        { threshold: 0.15 }
    );
    document.querySelectorAll('.rv').forEach(function (el) {
        io.observe(el);
    });

    var co = new IntersectionObserver(
        function (es) {
            es.forEach(function (e) {
                if (!e.isIntersecting) {
                    return;
                }
                co.unobserve(e.target);
                var n = +e.target.dataset.n,
                    c = 0,
                    s = Math.ceil(n / 60);
                var t = setInterval(function () {
                    c = Math.min(n, c + s);
                    e.target.textContent = c + (n >= 1000 ? '+' : '');
                    if (c >= n) {
                        clearInterval(t);
                    }
                }, 25);
            });
        },
        { threshold: 0.5 }
    );
    document.querySelectorAll('[data-n]').forEach(function (el) {
        co.observe(el);
    });
});

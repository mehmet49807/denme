<div id="gkToastHost" class="gk-toast-host" aria-live="polite" aria-atomic="true"></div>
<script>
window.gkToast = function (message, type) {
    var host = document.getElementById('gkToastHost');
    if (!host || !message) return;
    var el = document.createElement('div');
    el.className = 'gk-toast gk-toast--' + (type || 'info');
    el.textContent = String(message);
    host.appendChild(el);
    requestAnimationFrame(function () { el.classList.add('is-in'); });
    setTimeout(function () {
        el.classList.remove('is-in');
        el.classList.add('is-out');
        setTimeout(function () { el.remove(); }, 280);
    }, 3200);
};
document.addEventListener('DOMContentLoaded', function () {
    var flash = document.querySelector('.flash-success, .chat-flash-success, .dm-inbox-flash');
    if (flash && flash.textContent.trim()) {
        window.gkToast(flash.textContent.trim(), 'success');
    }
});
</script>

(function () {
    var btn = document.getElementById('inviteStoryKitBtn');
    var input = document.getElementById('inviteUrl');
    if (!btn || !input) return;

    function drawStoryKit(inviteUrl, username) {
        var canvas = document.createElement('canvas');
        canvas.width = 1080;
        canvas.height = 1920;
        var ctx = canvas.getContext('2d');

        var grad = ctx.createLinearGradient(0, 0, 1080, 1920);
        grad.addColorStop(0, '#2d1b4e');
        grad.addColorStop(0.45, '#7c3aed');
        grad.addColorStop(1, '#db2777');
        ctx.fillStyle = grad;
        ctx.fillRect(0, 0, 1080, 1920);

        ctx.fillStyle = 'rgba(255,255,255,0.08)';
        ctx.beginPath();
        ctx.arc(900, 220, 220, 0, Math.PI * 2);
        ctx.fill();
        ctx.beginPath();
        ctx.arc(160, 1600, 280, 0, Math.PI * 2);
        ctx.fill();

        ctx.fillStyle = '#fff';
        ctx.font = '700 64px Georgia, "Times New Roman", serif';
        ctx.textAlign = 'center';
        ctx.fillText('Gönül Köprüsü', 540, 420);

        ctx.font = '600 40px system-ui, sans-serif';
        ctx.fillStyle = 'rgba(255,255,255,0.88)';
        ctx.fillText('Davet linkimle katıl', 540, 520);

        if (username) {
            ctx.font = '700 48px system-ui, sans-serif';
            ctx.fillStyle = '#fde68a';
            ctx.fillText('@' + username, 540, 620);
        }

        ctx.fillStyle = 'rgba(255,255,255,0.14)';
        roundRect(ctx, 90, 780, 900, 280, 36);
        ctx.fill();

        ctx.fillStyle = '#fff';
        ctx.font = '600 34px ui-monospace, monospace';
        wrapText(ctx, inviteUrl, 540, 900, 820, 46);

        ctx.font = '600 36px system-ui, sans-serif';
        ctx.fillStyle = 'rgba(255,255,255,0.9)';
        ctx.fillText('Hikâyene ekle · Linki yapıştır', 540, 1280);

        ctx.font = '500 30px system-ui, sans-serif';
        ctx.fillStyle = 'rgba(255,255,255,0.7)';
        ctx.fillText('gonulkoprusu.com', 540, 1750);

        return canvas;
    }

    function roundRect(ctx, x, y, w, h, r) {
        ctx.beginPath();
        ctx.moveTo(x + r, y);
        ctx.arcTo(x + w, y, x + w, y + h, r);
        ctx.arcTo(x + w, y + h, x, y + h, r);
        ctx.arcTo(x, y + h, x, y, r);
        ctx.arcTo(x, y, x + w, y, r);
        ctx.closePath();
    }

    function wrapText(ctx, text, x, y, maxWidth, lineHeight) {
        var words = String(text).split('');
        var line = '';
        var yy = y;
        for (var n = 0; n < words.length; n++) {
            var test = line + words[n];
            if (ctx.measureText(test).width > maxWidth && line) {
                ctx.fillText(line, x, yy);
                line = words[n];
                yy += lineHeight;
            } else {
                line = test;
            }
        }
        ctx.fillText(line, x, yy);
    }

    btn.addEventListener('click', function () {
        var url = input.value || btn.getAttribute('data-share-url') || '';
        var username = btn.getAttribute('data-username') || '';
        var canvas = drawStoryKit(url, username);
        canvas.toBlob(function (blob) {
            if (!blob) return;
            var a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = 'gonul-koprusu-davet-hikaye.png';
            document.body.appendChild(a);
            a.click();
            a.remove();
            URL.revokeObjectURL(a.href);

            if (navigator.clipboard && url) {
                navigator.clipboard.writeText(url).catch(function () {});
            }
            btn.textContent = 'İndirildi · Link kopyalandı';
            setTimeout(function () {
                btn.textContent = 'Story görseli indir';
            }, 2400);

            if (window.gkTrack) {
                window.gkTrack('invite_share', { method: 'story_kit', event_category: 'growth' });
            }
        }, 'image/png');
    });
})();

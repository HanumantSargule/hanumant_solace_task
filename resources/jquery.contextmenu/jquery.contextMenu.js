

if (window.jQuery) {
    (function ($) {

        $.extend($.fn, {

            contextMenu: function (o, callback) {

                if (!o || !o.menu) return false;

                o.inSpeed = o.inSpeed ?? 150;
                o.outSpeed = o.outSpeed ?? 75;

                if (o.inSpeed === 0) o.inSpeed = -1;
                if (o.outSpeed === 0) o.outSpeed = -1;

                return this.each(function () {

                    const el = $(this);
                    const offset = el.offset();
                    const menu = $('#' + o.menu).addClass('contextMenu');

                    el.on('contextmenu', function (e) {
                        e.preventDefault();

                        if (el.hasClass('disabled')) return;

                        $('.contextMenu').hide();

                        const x = e.pageX;
                        const y = e.pageY;

                        menu
                            .css({ top: y, left: x })
                            .fadeIn(o.inSpeed);

                        $(document).off('click keydown')
                            .on('click', hideMenu)
                            .on('keydown', keyHandler);

                        menu.find('a').off('click').on('click', function () {
                            hideMenu();
                            callback?.(
                                $(this).attr('href').substring(1),
                                el,
                                {
                                    x: x - offset.left,
                                    y: y - offset.top,
                                    docX: x,
                                    docY: y
                                }
                            );
                            return false;
                        });
                    });

                    function hideMenu() {
                        $(document).off('click keydown');
                        menu.fadeOut(o.outSpeed);
                    }

                    function keyHandler(e) {
                        const items = menu.find('li:not(.disabled)');
                        const current = menu.find('li.hover');

                        if (e.key === 'ArrowDown') {
                            e.preventDefault();
                            (current.length ? current.removeClass('hover').nextAll(items).first() : items.first())
                                .addClass('hover');
                        }
                        if (e.key === 'ArrowUp') {
                            e.preventDefault();
                            (current.length ? current.removeClass('hover').prevAll(items).first() : items.last())
                                .addClass('hover');
                        }
                        if (e.key === 'Enter') current.find('a').trigger('click');
                        if (e.key === 'Escape') hideMenu();
                    }
                });
            },

            disableContextMenu() {
                return this.addClass('disabled');
            },

            enableContextMenu() {
                return this.removeClass('disabled');
            }

        });

    })(jQuery);
}

// Javascript functions for onetopic course format

M.course = M.course || {};

M.course.format = M.course.format || {};

M.course.format.showInfo = function(id) {

    new M.core.dialogue({
                draggable : true,
                headerContent : '<span>' + M.str.moodle.info + '</span>',
                bodyContent : Y.Node.one('#' + id),
                centered : true,
                width : '480px',
                modal : true,
                visible : true
            });

    Y.Node.one('#' + id).show();

};

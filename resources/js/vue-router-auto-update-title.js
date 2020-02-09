const DEFAULT_TITLE = '东莞理工学院城市学院';

const TITLE_SUFFIX = DEFAULT_TITLE;

function getTitleValue(title, vueRouterInstance) {
    if (typeof title === "string")
        return title;
    return title(vueRouterInstance);
}

window.vueRouterCounter = 0;

export default function (vueRouterInstance, defaultTitle = DEFAULT_TITLE, titleSuffix = TITLE_SUFFIX) {
    vueRouterInstance.beforeEach(function (to, from, next) {
        if (to.hasOwnProperty('meta')) {
            if (to.meta.hasOwnProperty('title')) {
                document.title = getTitleValue(to.meta.title, vueRouterInstance) + ' - ' + titleSuffix;
            } else if (to.meta.hasOwnProperty('i18nTitle')) {
                document.title = vueRouterInstance.app.$t(to.meta.i18nTitle) + ' - ' + titleSuffix;
            } else {
                document.title = DEFAULT_TITLE;
            }
        } else {
            document.title = DEFAULT_TITLE;
        }
        ++window.vueRouterCounter;
        next();
    });
}

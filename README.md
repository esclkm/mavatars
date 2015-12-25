mavatars
========

Модуль прикрепления изображений, файлов к страницам.
Версия 3 была существенно обновлена:
1. возможность загрузки любых файлов доступных в mimetype.php
2. Использование fileapi https://github.com/RubaXa/jquery.fileapi
3. Обрезка изображений на стороне клиента, если изображение больше 1600 * 1600 (снижение нагрузки на сервер)
4. счетчик загрузки
5. Создние миниатюр c помощью AJAX

Требования
----------------

Для корректной работы необходима Cotonti Siena > 0.9.18
В противном случае установите шаблонизатор из репоззитория гитхуб
https://github.com/Cotonti/Cotonti/blob/master/system/cotemplate.php
(исправлена работа шаблонизатора с объектами)

Использование:
-----------------------

Добавьте в шаблон page.add.tpl:

    {PAGEADD_FORM_MAVATAR}

Добавьте в шаблон page.edit.tpl:

    {PAGEEDIT_FORM_MAVATAR}

В шаблон page.tpl для вывода изображений:

    <!-- IF {PAGE_MAVATAR} -->
    <hr/>
    <div class="row">
        <!-- FOR {KEY}, {VALUE} IN {PAGE_MAVATAR} -->
        <div class="col-md-3 grid-sizer">
            <a href="{VALUE.check_thumb_1140_755_width}"  class="fancybox" rel="gallery1" >
                <img src="{VALUE|cot_mav_thumb($this, 1140, 755, width)}" class="img-responsive" alt="{VALUE.DESC}" title="{VALUE.TEXT}"/>
            </a>
            {VALUE.DESC}
        </div>
        <!-- ENDFOR -->
    </div>
    <div class="clear"></div>
    <!-- ENDIF -->

 В шаблон page.tpl для вывода файлов для скачивания:

     <!-- IF {PAGE_MAVATARFILES} -->
    <hr/>
    <div class="row">
        <!-- FOR {KEY}, {VALUE} IN {PAGE_MAVATARFILES} -->
        <div class="col-md-3 grid-sizer">
            <a href="{VALUE.DOWNLOAD}" >
                <span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span> {VALUE.DESC}
            </a>
        </div>
        <!-- ENDFOR -->
    </div>
    <div class="clear"></div>
    <!-- ENDIF -->

Использование в других шаблонах, которые выводят страницы аналогично шаблону page.tpl

<?php
// Функция ищет все документы, которые ссылаются на данную запись
// Рекурсивная функция
function custom_related_records_and_posts($id_post) {
    $fields_func = get_field_objects($id_post);
        if( $fields_func ) {
        ?>
            <div class="entry-content">
            <?php
                // Формирую запрос на вывод всех произвольных типов записи
                $args = array(
                    'public'   => true,
                    '_builtin' => false
                );
                $output   = 'names'; // names or objects, note names is the default
                $operator = 'and';   // 'and' or 'or'
                $post_types = get_post_types( $args, $output, $operator);
                // Пробегаюсь по всем произвольным типам записи
                // Чтобы посмотреть поработала ли текущая структура с документами
                unset($tab_array); // $tab_array is gone
                $tab_array = []; // $tab_array is here again
                foreach ( $post_types as $post_type ) {
                    $obj = get_post_type_object( $post_type );
                    $all_docs = get_posts(array(
                            'post_type' => $post_type,
                            'numberposts'=> -1,
                            'posts_per_page'=> -1
                        ));
                    $i = 0; // Счетчик наличия документа созданного Текущим get_the_ID() подразделением
                    unset($tabcont_array); // $tab_array is gone
                    $tabcont_array = []; // $tab_array is here again
                    foreach ($all_docs as $all_doc) {
                        global $post; // Устанавливает $post (глобальная переменная - объект поста)
                        // Перенаправим полученные данные поста в переменную $post и не в какую другую.
                        $post = $all_doc;
                        setup_postdata( $post );
                        $fields = get_field_objects($post->ID); // Возвращает массив полей для конкретного поста
                        // check if the flexible content field has rows of data
                        if( have_rows('vls_acf_relates_documents_ver_doc',$post->ID) ) {
//                        echo '<br>У ЭТОГО ЕСТЬ: '.$post_type.'<br>';
                            $for_check = get_field('vls_acf_relates_documents_ver_doc',$post->ID); // Проверяем необходимое поле
                                if (!is_null($for_check)) {
                                    $kkk = get_post( $post->ID ); // Для проверки не закрепленных записей за структурой
                                    $title_kkk = $kkk->post_title; // Для проверки не закрепленных записей за структурой (Вывод заголовка), по ID тяжело искать
                                    $creators_reports = $fields['vls_acf_relates_documents_ver_doc']['value'];
                                    foreach ($creators_reports as $creator_report) {
                                        if (is_object($creator_report['vls_acf_relates_documents_ver_doc_post_connect'])) {
                                            $id_creator_report = $creator_report['vls_acf_relates_documents_ver_doc_post_connect']->ID;
                                            // if (get_the_ID() === $id_creator_report) { //альтернатива
                                            if ($id_post === $id_creator_report) {
                                                $i++;
                                                $tabcont_array[] = $post->ID;
                                            }

                                        }
                                    }
                                }
                            }
                        }
                        wp_reset_postdata(); // сбрасывает $post
                        $tab_array[] = [$obj->name => $tabcont_array];
                    }
                    // Подготавливаем и очищаем массив, к котором содержаться результаты поиска
                    foreach ($tab_array as $key =>$tab_tabs) {
                        foreach ($tab_tabs as $tab_tab) {
                            if (count($tab_tab) == 0) {
                                unset($tab_array[$key]);
                            }
                        }
                    }
                    sort($tab_array); // Пересчитываем индексы так как массив большой и много пустых массивов было, которые удалили
                    // Собираем табы
                    // Имена табов

                    //    echo '<br>';
                    //    echo '$TAB_ARRAY = ';
                    //    print_r($tab_array);
                    //    echo '<br>';

                    echo '<div class="row">';
                        echo '<div class="col-3">';
                            echo '<div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">';
                                echo '<a class="nav-link active" id="v-pills-home-tab" data-toggle="pill" href="#v-pills-home" role="tab" aria-controls="v-pills-home" aria-selected="true">Сведения</a>';
                                foreach ($tab_array as $keys => $tab_tab) {
                                    foreach ($tab_tab as $name_tab => $content_tabs) {
                                        $obj = get_post_type_object( $name_tab );
                                        echo '<a class="nav-link" id="v-pills-'.$name_tab.'-tab" data-toggle="pill" href="#v-pills-'.$name_tab.'" role="tab" aria-controls="v-pills-'.$name_tab.'" aria-selected="false">'.$obj->labels->name.'</a>';
                                    }
                                }
                            echo '</div>';
                        echo '</div>';
                        echo '<div class="col-9">';
                            echo '<div class="tab-content" id="v-pills-tabContent">';
                                echo '<div class="tab-pane fade show active" id="v-pills-home" role="tabpanel" aria-labelledby="v-pills-home-tab">';
                                ?>
                                <?php
                                    the_content();
                                ?>

                                <?php
                            echo '</div>';
                    // Содержимое табов
                    foreach ($tab_array as $keys => $tab_tab) {
                        foreach ($tab_tab as $name_tab => $content_tabs) {
                            echo '<div class="tab-pane fade" id="v-pills-'.$name_tab.'" role="tabpanel" aria-labelledby="v-pills-'.$name_tab.'-tab">';
                            echo '<table class="table table-hover">';
                            echo '<thead  class="thead-light">';
                            echo '<tr>';
                            echo '<th scope="col" style="width: 50px;">№</th>';
                            echo '<th scope="col">Наименование</th>';
                            echo '</tr>';
                            echo '</thead>';
                            echo '<tbody>';
                            $i = 0;
                            foreach ($content_tabs as $content_tab) {
                                $post_id = get_post( $content_tab, ARRAY_A);
                                $title = $post_id['post_title'];
                                echo '<tr>';
                                echo '<th scope="row">'.++$i.'</th>';
                                echo '<td><a href="'.$post_id['guid'].'">'.$post_id['post_title'].'</a></td>';
                                echo '</tr>';
                            }
                        }
                        echo '</tbody>';
                        echo '</table>';
                        echo '</div>';
                    }
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                    ?>



                    <?php
                    wp_link_pages(
                        array(
                            'before'      => '<div class="page-links"><span class="page-links-title">' . __( 'Pages:', 'twentysixteen' ) . '</span>',
                            'after'       => '</div>',
                            'link_before' => '<span>',
                            'link_after'  => '</span>',
                            'pagelink'    => '<span class="screen-reader-text">' . __( 'Page', 'twentysixteen' ) . ' </span>%',
                            'separator'   => '<span class="screen-reader-text">, </span>',
                        )
                    );
                    ?>
                </div><!-- .entry-content -->
            <?php
        }
        }






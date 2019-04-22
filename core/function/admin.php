<?php
/**
 * Created by PhpStorm.
 * User: thanhtai
 * Date: 17/04/2019
 * Time: 14:50
 */

/**
 * Show view for edit option plugin
 *
 * @return string
 */
defined('ABSPATH') or die('No script kiddies please!');

function vnaUpdateOption()
{
    vnaCheckValid();
    vnaHandleUpdate();

    $option = get_option(TDC_OPTION_NAME);


    $html = '<div class="wrap"><h2>VIET NAM AFFILIATE TOOL</h2>';
    $html .= '<p>Viet Nam Affiliate là ứng dụng giúp chuyển đổi link trên website của bạn thành affiliate link có kèm checking cho bạn xem báo cáo.</p>';


    $html .= ' <a href="?page=viet-nam-afffiliate-report.php" class="button-primary">Xem Report</a>';
    $html .= ' <a href="https://toidicode.com?utm=affiliate_wp" class="button-primary">Xem Hướng dẫn</a>';
    $html .= ' <a href="https://toidicode.com?utm=affiliate_wp" class="button-primary">Xem thêm plugin</a>';

    $html .= '<form method="post" action="">';

    $html .= '<table class="form-table"><tbody>';

    $html .= '<tr>';
    $html .= '<th scope="row"><label for="affiliateID">Affiliate ID</label></th>';
    $html .= '<td><input type="text" placeholder="Affiliate ID" id="affiliateID" class="regular-text" name="options[affiliateID]" value="' . $option['affiliateID'] . '"></td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<th scope="row"><label for="deeplink">DeepLink</label></th>';
    $html .= '<td><textarea type="text" placeholder="Affiliate ID" id="deeplink" class="regular-text code" name="options[deepLinkURL]" spellcheck="false" rows="3" style="resize: none;">' . $option['deepLinkURL'] . '</textarea>';
    $html .= '<p class="description">';
    $html .= '<code style="color: red">{{AFF_ID}}</code>: Là đoạn mã sẽ chuyền Affiliate ID của bạn vào (<strong>Bắt buộc</strong>) <br>';
    $html .= '<code style="color: red">{{URL}}</code>: Là đoạn mã sẽ chuyền URL mà bạn muốn chuyển vào (<strong>Bắt buộc</strong>) <br>';
    $html .= 'VD: link trên sẽ thành <code>' . str_replace('{{AFF_ID}}', $option['affiliateID'], str_replace('{{URL}}', 'https://shopee.vn/flash_sale', $option['deepLinkURL'])) . '</code>';
    $html .= '</p>';
    $html .= '</td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<th scope="row"><label for="domain">Domain</label></th>';
    $html .= '<td><textarea name="options[domain]" id="domain" cols="30" rows="6" spellcheck="false"  class="regular-text">' . implode(',', is_array($option['domain']) ? $option['domain'] : []) . '</textarea>';
    $html .= '<p class="description">Danh sách domain sẽ chuyển đổi thành affiliate link - đặt cách nhau bằng dấu <code>,</code> <br/><strong style="color: red">Chú ý: chỉ điền domain.</strong></p>';
    $html .= '</td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<th scope="row"></th>';
    $html .= '<td><input type="checkbox" name="options[newtab]" value="1" id="newtab"  ' . ($option['newtab'] ? 'checked' : '') . '><label for="newtab">Mở tab mới khi click vào link</label></td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<th scope="row"></th>';
    $html .= '<td><input type="checkbox" name="options[rel]" value="1" id="rellink" ' . ($option['rel'] ? 'checked' : '') . '><label for="rellink">Thêm <code>rel=nofollow</code> vào link</label></td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<th scope="row"></th>';
    $html .= '<td><input type="checkbox" name="options[tracking]" value="1" id="tracking" ' . ($option['tracking'] ? 'checked' : '') . '><label for="tracking">Bật tracking.</label></td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<th scope="row"></th>';
    $html .= '<td><input type="submit" name="submit" id="submit" class="button button-primary" value="Cập Nhật">';
    $html .= '</tr>';


    $html .= '</tbody></table>';
    $html .= '</form>';
    $html .= '</div>';

    return $html;
}

/**
 *
 * Show report
 *
 */
function vnaShowReport()
{
    $html = '<div> <a href="?page=viet-nam-afffiliate-report.php" class="button-primary">Cấu hình</a>';
    $html .= ' <a href="https://toidicode.com?utm=affiliate_wp" class="button-primary">Xem Hướng dẫn</a>';
    $html .= ' <a href="https://toidicode.com?utm=affiliate_wp" class="button-primary">Xem thêm plugin</a></div>';
    echo "<div class='wrap'>";
    echo "<h2>VIET NAM AFFILIATE TOOL</h2>";
    echo $html;
    vnaCheckValid();

    // handle query
    global $wpdb;
    $items_per_page = 50;
    $page = isset($_GET['cpage']) ? abs((int)$_GET['cpage']) : 1;
    $offset = ($page * $items_per_page) - $items_per_page;
    $postsTable = $wpdb->prefix . 'posts';
    $vnaLinkTable = $wpdb->prefix . 'tdc_link';


    $query = "SELECT ${vnaLinkTable}.*, ${postsTable}.post_title FROM $vnaLinkTable JOIN $postsTable on ${postsTable}.id = ${vnaLinkTable}.post_id  ORDER BY ${vnaLinkTable}.date DESC";
    $total_query = "SELECT COUNT(1) FROM (${query}) AS combined_table";
    $total = $wpdb->get_var($total_query);

    $results = $wpdb->get_results($query . ' LIMIT ' . $offset . ', ' . $items_per_page, OBJECT);

    if ($results):

        echo "<table id='vna-report' class='cell-border'>
         <thead>
            <tr>
                <th>NO</th>
                <th>LINK</th>
                <th>POST</th>
                <th>DATE (YYYYMMDD)</th>
                <th>CLICKED AT</th>
                <th>IP</th>
            </tr>
         </thead>
         <tbody>";
        foreach ($results as $result):
            echo "<tr><td>$result->id</td><td>$result->link</td><td><a href='/$result->post_id' class='button-link'>$result->post_title</a></td><td>$result->date</td><td>$result->created_at</td><td>$result->ip</td></tr>";
        endforeach;

        echo "</tbody></table>";

        // get page
        echo "<style>.page-numbers{margin: 2px;padding: 5px 9px; border: 1px solid #d1d1d1}.page-numbers.dots{border: none}.page-numbers.current{background-color: #00509e;color: #ffffff}</style>";
        echo "<div style='margin-top: 20px;text-align: right'>";
        echo paginate_links(array(
            'base' => add_query_arg('cpage', '%#%'),
            'format' => '',
            'prev_text' => __('&laquo;'),
            'next_text' => __('&raquo;'),
            'total' => ceil($total / $items_per_page),
            'current' => $page,
        ));
        echo "</div>";
    else:
        echo "<h3 style='text-align: center'>Chưa có dữ liệu</h3>";
    endif;

    echo "</div>";
}

/**
 * handle update option
 *
 */
function vnaHandleUpdate()
{
    if (!empty($_POST['submit'])) {
        if (!is_admin()) {
            echo "<div class=\"error notice\">
                          <p>Bạn phải là admin thì mới chạm vào em nó được</p>
                      </div>";
            return;
        }

        $option = get_option(TDC_OPTION_NAME);

        $optionRequest = $_POST['options'];

        foreach ($option as $key => $value) {
            if (!empty($optionRequest[$key])) {
                $option[$key] = $optionRequest[$key];
            }

            if ($key == 'domain') {
                $option['domain'] = explode(',', $optionRequest['domain']);
            }

            if (in_array($key, ['rel', 'newtab', 'tracking'])) {
                $option['rel'] = $optionRequest['rel'] == '1' ? true : false;
                $option['newtab'] = $optionRequest['newtab'] == '1' ? true : false;
                $option['tracking'] = $optionRequest['tracking'] == '1' ? true : false;
            }

            if ($key == 'deepLinkURL' && strpos($optionRequest['deepLinkURL'], '{{AFF_ID}}') === false && strpos($optionRequest['deepLinkURL'], '{{URL}}') == false) {

                echo "<div class=\"error notice\">
                          <p>DeepLink phải chứa <code>{{AFF_ID}}</code> và <code>{{URL}}</code></p>
                      </div>";
                return;
            }
        }

        update_option(TDC_OPTION_NAME, $option);
    }
}


/**
 * check exist table in database
 *
 * @return false|int
 */
function vnaCheckValid()
{
    global $wpdb;

    if (!$wpdb->query('SELECT 1 FROM ' . $wpdb->prefix . 'tdc_link LIMIT 1 ')) {

        echo "<div class=\"error notice\">
                          <p>Table của plugin không tồn tại, làm ơn kiểm tra lại</p>
                      </div>";
        return false;
    }

    return true;
}
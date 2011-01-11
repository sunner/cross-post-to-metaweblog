<?php
/*
Plugin Name: Cross Post to MetaWeblog
Plugin URI: https://github.com/sunner/cross-post-to-metaweblog
Description: Sync post to another blog through MetaWeblog API.
Version: 0.1
Author: Sun Zhigang
Author URI: http://sunner.cn
License: GPL2
*/

/*  Copyright YEAR  PLUGIN_AUTHOR_NAME  (email : PLUGIN AUTHOR EMAIL)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
// Cross post when publish/edit posts
add_action ( 'publish_post', 'cross_post' );

function cross_post($postid) {
    $origin_link = get_permalink($postid);
    require('config.inc');

    $post = & get_post($postid);

    $struct['title'] = $post->post_title;
    $struct['description'] = wpautop($post->post_content.$P2M_APPEND);

    $cross_id = get_post_meta($postid, 'p2m_crossid', true);
    if (empty($cross_id)) {
        $method = 'metaWeblog.newPost';
        $id = 0;
    } else {
        $method = 'metaWeblog.editPost';
        $id = $cross_id;
    }
    $request = xmlrpc_encode_request($method, array($id, $P2M_USERNAME, $P2M_PASSWORD, $struct, $P2M_PUBLISH), array('encoding' => 'UTF-8', 'escaping' => 'cdata'));

    $context = stream_context_create(array('http' => array(
        'method' => "POST",
        'header' => "Content-Type: text/xml",
        'content' => $request
    )));

    $file = file_get_contents($P2M_URL, false, $context);
    if ($id == 0) { 
        $cross_id = xmlrpc_decode($file, 'UTf-8');  //Get post id in the remote blog
        if (!xmlrpc_is_fault($cross_id)) {
            add_post_meta($postid, 'p2m_crossid', $cross_id, true);
        }
    }

    return $postid;
}

?>

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
add_action ( 'publish_post', 'cross_post' );

function cross_post($postid) {
    $origin_link = 'http://blog.sunner.cn/?p='.$postid;
    require('config.inc');

    $post = & get_post($postid);

    $struct['title'] = $post->post_title;
    $struct['description'] = $post->post_content.$P2M_APPEND;

    $request = xmlrpc_encode_request('metaWeblog.newPost', array(0, $P2M_USERNAME, $P2M_PASSWORD, $struct, true), array('encoding' => 'UTF-8', 'escaping' => 'cdata'));

    $context = stream_context_create(array('http' => array(
        'method' => "POST",
        'header' => "Content-Type: text/xml",
        'content' => $request
    )));

    $file = file_get_contents($P2M_URL, false, $context);

/*
    $f = fopen('/tmp/info', 'w');
    fprintf($f, $request);
    fprintf($f, "%s", $file);
*/
 
    return $postid;
}

?>

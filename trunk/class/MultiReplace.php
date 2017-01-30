<?php

class MultiReplace {

    /**
     * @since 1.0.0
     * @return void
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'registerAdminPages' ) );
    }

    public function registerAdminPages() {
        add_submenu_page( 'tools.php', 'MultiReplace', 'MultiReplace', 'manage_options', 'multireplace', array( $this, 'multiReplacelAdminPage' ), '' );
    }

    public function multiReplacelAdminPage() {
        $search = filter_input( INPUT_POST, 'multireplace-search' );
        $replace = filter_input( INPUT_POST, 'multireplace-replace' );
        $replaceIn = filter_input( INPUT_POST, 'multireplace-replace-in', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

        echo '<div class="card">';

        echo '<h2>MultiReplace</h2>';

        echo '<form action="' . $_SERVER['PHP_SELF'] . '?page=multireplace" method="post">';
        echo 'Search:<br /> <input type="text" name="multireplace-search" value="'.$search.'"/><br /><br />';
        echo 'Replace<br /> <input type="text" name="multireplace-replace"  value="'.$replace.'" /><br /><br />';

        echo 'Replace in<br />';
        echo '<select name="multireplace-replace-in[]" multiple>';
        echo '<option>Postmeta</option>';
        echo '<option>Options</option>';
        echo '<option>Posts</option>';
        echo '</select>';

        echo '<br /><br />';

        echo '<input type="submit" value="Do it!" />';
        echo '</form>';


        if ( !empty( $search ) && !empty( $replaceIn )  ) {
            if ( in_array( 'Postmeta', $replaceIn ) ) {
                $this->replacePostMeta( $search, $replace );
                echo 'Post meta replaced<br />';
            }

            if ( in_array( 'Options', $replaceIn ) ) {
                $this->replaceOptions( $search, $replace );
                echo 'Options replaced<br />';
            }

            if ( in_array( 'Posts', $replaceIn ) ) {
                $this->replacePosts( $search, $replace );
                echo 'Posts replaced<br />';
            }
        } else if ( !empty( $search ) ) {
            echo 'Nothing selected.<br />';
        }

        echo '</div>';
    }

    /**
     * Replace posts
     *
     * @param $search
     * @param $replace
     */
    public function replacePosts( $search, $replace ) {
        $allPosts = get_posts(
            array(
                'numberposts' => -1,
                'post_type' => get_post_types(),
            )
        );

        foreach ( $allPosts as $replacePost ) {
            wp_update_post( array(
                'ID' => $replacePost->ID,
                'post_content' => $this->replace( $replacePost->post_content, $search, $replace ),
                'post_title' => $this->replace( $replacePost->post_title, $search, $replace )
            ) );
        }
    }

    /**
     * Replace options
     *
     * @param $search
     * @param $replace
     */
    public function replaceOptions( $search, $replace ) {
        global $wpdb, $table_prefix;

        $results = $wpdb->get_results( "SELECT * FROM " . $table_prefix . "options" );

        foreach ( $results as $result ) {
            $valueUnserialized = maybe_unserialize( $result->option_value );
            $valueReplaced = $this->replace( $valueUnserialized, $search, $replace );

            update_option( $result->option_name, $valueReplaced, $result->autoload );
        }
    }

    /**
     * Replace post meta
     *
     * @param $search
     * @param $replace
     */
    public function replacePostMeta( $search, $replace ) {
        global $wpdb, $table_prefix;

        $results = $wpdb->get_results( "SELECT * FROM " . $table_prefix . "postmeta" );

        foreach ( $results as $result ) {
            $valueUnserialized = maybe_unserialize( $result->meta_value );
            $valueReplaced = $this->replace( $valueUnserialized, $search, $replace );

            update_post_meta( $result->post_id, $result->meta_key, $valueReplaced );
        }
    }


    /**
     * Recursive function to replace strings, array and objects
     *
     * @param $needle
     * @param $search
     * @param $replace
     * @return array|mixed
     */
    public function replace( $needle, $search, $replace ) {
        if ( is_object( $needle ) || is_array( $needle ) ) {
            // scan recursive
            foreach ( $needle as $key => $value ) {
                if ( is_array( $needle ) ) {
                    $needle[$key] = $this->replace( $value, $search, $replace );
                } else {
                    $needle->{$key} = $this->replace( $value, $search, $replace );
                }
            }
        } else {
            $needle = str_replace( $search, $replace, $needle );
        }

        return $needle;
    }
}

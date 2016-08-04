<?php
/**
 * The template for displaying the your-website form.
 *
 * @package as-mt-trial
 * @version 1.0
 */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">

        <?php
            $error = filter_input( INPUT_GET, 'error', FILTER_SANITIZE_STRING );
            $success = filter_input( INPUT_GET, 'success', FILTER_SANITIZE_STRING );
            $error_message = null;

            if ( 'missing_fields' === $error ) {
                $error_message = __( 'All fields are required. Please try again.', 'as-mt-trial' );
            }

            if ( 'invalid_url' === $error ) {
                $error_message = __( 'We were unable to process the URL you provided. Please check your entry and try again.', 'as-mt-trial' );
            }

            if ( 'server' === $error ) {
                $error_message = __( 'We were unable to save your submission. Please try again.', 'as-mt-trial' );
            }

            if ( 'true' === $success ) {
                $success_message = __( 'Thank you for your submission!', 'as-mt-trial' );
            }
        ?>

        <?php if ( ! empty( $error_message ) ) : ?>
            <div class="website-form-error">
                <p><?php echo $error_message; ?></p>
            </div>
        <?php endif; ?>

        <?php if ( ! empty( $success_message ) ) : ?>
            <div class="website-form-success">
                <p><?php echo $success_message; ?></p>
                <p><a href="<?php echo esc_url( site_url( 'your-website' ) ); ?>"><?php echo __( 'Submit another website.', 'as-mt-trial' ); ?></a></p>
            </div>
        <?php endif; ?>

        <?php if ( empty( $success_message ) ) : ?>
        <div class="website-form-wrapper">
            <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">

                <label for="name">Website Name
                    <input type="text" name="name" id="name" placeholder="<?php esc_attr_e( 'Enter your website name...' ); ?>" />
                </label>

                <label for="url">Website URL
                    <input type="text" name="url" id="url" placeholder="<?php esc_attr_e( 'e.g. http://google.com' ); ?>">
                </label>

                <?php wp_nonce_field( 'submit_website', 'submit_website_nonce' ); ?>

                <input type="hidden" name="action" value="submit_website">

                <input type="submit" value="Submit Website" />
            </form>
        </div>
        <?php endif; ?>

    </main><!-- .site-main -->

    <?php get_sidebar( 'content-bottom' ); ?>

</div><!-- .content-area -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>

<style type="text/css">
    .overlay-container {
        position: absolute;
        z-index: 1;
        height: 100%;
        width: 100%;
        top: 20%;
        left: 0;
        background: #c1bbbb45;
    }

    .overlay-text {
        position: relative;
        top: 20%;
        left: 0%;
        color: white;
        text-align: center;
        font-size: x-large;
        font-weight: bolder;
        text-shadow: 2px 2px #1b1515;
    }
</style>


<div id="overlay" class="overlay-container">
    <p id="ov_text" class="overlay-text">
        <?php echo __( 'This feature is available in the PRO version.', 'wp-edunext-marketing-site' ); ?> </p>
</div>

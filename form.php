<div class="sc_box">
    <style scoped>
        .sc_box{
            display: grid;
            grid-template-columns: max-content 1fr;
            grid-row-gap: 10px;
            grid-column-gap: 20px;
        }
        .sc_field{
            display: contents;
        }
    </style>
    <p class="meta-options sc_field">
        <label for="sc_code_snippet">Code</label>
        <textarea id="sc_code_snippet" type="text" rows="30" name="sc_code_snippet"><?php echo esc_attr( get_post_meta( get_the_ID(), 'sc_code_snippet', true ) ); ?></textarea>
    </p>
</div>


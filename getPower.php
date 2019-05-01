<select name="g_month" id="id_power">
    <option value="0" <?php if(empty(getFormData('g_month'))) echo 'selected="selected"'; ?>>▶︎選択してください</option> 
        <?php
        for($i = 1; $i < 32; $i++){
        ?>
            <option value="<?php echo $i ?>" <?php if(getFormData('g_month') == $i) echo 'selected="selected"'; ?>><?php echo $i ?></option>;
        <?php
        }
        ?>
    </select>

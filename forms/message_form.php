<fieldset>
    <div class="form-group">
        <label for="l_name">Message</label>
        <input type="text" name="message_text" value="<?php echo $edit ? $users['message_text'] : ''; ?>" placeholder="Messsage" class="form-control" required="required" id="message_text">
    </div> 

    <div class="form-group">
        <label for="phone">Status</label>
            <select name="status" value="<?php echo $edit ? $users['status'] : ''; ?>" placeholder="status" class="form-control"  type="text" id="status"><option value="Y">Active</option><option value="N">InActive</option></select>
    </div> 

    
    <div class="form-group text-center">
        <label></label>
        <button type="submit" class="btn btn-warning" >Save <span class="glyphicon glyphicon-send"></span></button>
    </div>            
</fieldset>
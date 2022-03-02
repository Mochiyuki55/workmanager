
<div class="modal fade" id="modal2" tabindex="-1" role="dialog" aria-labelledby="label1" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">確認</h5>
        <!-- 閉じるボタン -->
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form class="form" action="" method="post">
        <div class="modal-body">
          <p>勤務実績　編集画面</p>
          <p><?php echo $edited_date['set_date1']."/".$edited_date['set_date2']; ?></p>
          <p>
            出勤時刻：<input type="time" name="" value="<?php echo $edited_date['start_at']; ?>"> <br>
            退勤時刻：<input type="time" name="" value="<?php echo $edited_date['end_at']; ?>"> <br>
            休憩時間：<input type="time" name="" value="<?php echo $edited_date['rest']; ?>">
          </p>
          <div class="my-2">
            <label><input type="radio" name="style[]" value="出社" <?php if($edited_date['style'] == "出社"){echo 'checked';} ?>> 出社　</label>
            <label><input type="radio" name="style[]" value="在宅" <?php if($edited_date['style'] == "在宅"){echo 'checked';} ?>> 在宅</label>
          </div>
          <div class="checkbox my-3">
            <label>
              <input type="checkbox" name="holyday" value="1"> 年休を使用する
            </label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">キャンセル</button>
          <button type="submit" class="btn btn-primary" data-dismiss="modal">OK</button>
        </div>
      </form>

    </div><!-- modal content -->
  </div>
</div>

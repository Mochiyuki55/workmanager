<div class="modal fade" id="modal1" tabindex="-1" role="dialog" aria-labelledby="label1" aria-hidden="true">
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
          <p>この勤務表を<?php echo $confirm_word; ?>しますか？</p>
        </div>

        <div class="modal-footer">
          <a type="button" class="btn btn-secondary text-light" data-dismiss="modal">キャンセル</a>
          <a href="" type="button" class="btn btn-primary" data-dismiss="modal">OK</a>
        </div>
      </form>

    </div><!-- modal content -->
  </div>
</div>

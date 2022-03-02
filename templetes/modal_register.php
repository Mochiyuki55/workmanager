<div class="modal fade" id="modal1" tabindex="-1" role="dialog" aria-labelledby="label1" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="label1">追加する社員を選択してください</h5>
        <!-- 閉じるボタン -->
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form class="form" action="" method="post">
        <div class="modal-body">
          <ul class="list-group">
            <li class="list-group-item"><input type="checkbox" name="player[]" id="name1"> <label for="name1">　〇〇　〇〇</label></li>
            <li class="list-group-item"><input type="checkbox" name="player[]" id="name2"> <label for="name2">　〇〇　〇〇</label></li>
            <li class="list-group-item"><input type="checkbox" name="player[]" id="name3"> <label for="name3">　〇〇　〇〇</label></li>
            <li class="list-group-item"><input type="checkbox" name="player[]" id="name4"> <label for="name4">　〇〇　〇〇</label></li>
            <li class="list-group-item"><input type="checkbox" name="player[]" id="name5"> <label for="name5">　〇〇　〇〇</label></li>
          </ul>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">キャンセル</button>
          <button type="submit" class="btn btn-primary" data-dismiss="modal">登録</button>
        </div>
      </form>

    </div>
  </div>
</div>

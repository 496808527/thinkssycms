(function (window, undefined) {
  var total;
  var loadsize;
  window.SimpleUpload = function (filedata) {
    this.file = filedata.file;
    this.fileName = filedata.name;
    this.fileurl = filedata.fileurl;
    this.fileSize = filedata.size;
    this.filetype = filedata.type;
    this.form = filedata.form;
    this.sendurl = filedata.sendurl;
    this.blockSize = 1024 * 1024 * 2;
    this.blockCount = Math.ceil(this.fileSize / this.blockSize);
    this.uploadid = filedata.uploadid;
    this.index = 0;
    this.path=filedata.savepath;
    total=this.fileSize;
  }

  SimpleUpload.prototype.uploadFile = function () {

  }

  SimpleUpload.prototype.uploadImage = function () {
    $(".fileinfo").html('<img src="' + this.fileurl + '" />');
  }

  SimpleUpload.prototype.Upload = function (obj) {
    ojb=obj.formdata?obj:this;
    formdata = new FormData();
    start = obj.index * obj.blockSize;
    end = Math.min(obj.fileSize, obj.blockSize + start);
    block = obj.file.slice(start, end);
    formdata = new FormData();
    formdata.append("path",obj.path);
    formdata.append("filename", obj.fileName);
    formdata.append("filetype", obj.filetype);
    formdata.append("size", end - start);
    formdata.append('data', block);
    formdata.append('index', obj.index);
    formdata.append('blockcount', obj.blockCount);
    formdata.append('uploadid', obj.uploadid);
    this.formdata = formdata;
    loadsize=end;
    if (this.filetype.indexOf("image") != -1) {
      this.uploadImage();
    } else {
      this.uploadFile();
    }
    this.sendFormdata();
  }

  SimpleUpload.prototype.sendFormdata = function () {
    simple = this;
    $.ajax({
      type: "post",
      url: simple.sendurl,
      data: simple.formdata,
      xhr: function () {
        myXhr = $.ajaxSettings.xhr();
        if (myXhr.upload) { //检查upload属性是否存在
          //绑定progress事件的回调函数
          myXhr.upload.addEventListener('progress', simple.progressHandlingFunction, false);
        }
        return myXhr; //xhr对象返回给jQuery使用
      },
      success: function (result) {
          
        if (result.state == "doing") {
          simple.index = result.index;
          simple.Upload(simple);
        } else if(result.state == "err"){
          layer.msg(result.msg, {icon: 1, time: 1000});
        }else {
          console.log(result);
          console.log(result.uploadid);
          $("#uploadid").val(result.uploadid);
          $("#filepath").val(result.filepath);
          layer.msg(result.msg, {icon: 1, time: 1000});
        }
      },
      contentType: false, //必须false才会自动加上正确的Content-Type
      processData: false //必须false才会避开jQuery对 formdata 的默认处理
    });
    
  }
  SimpleUpload.prototype.progressHandlingFunction = function (e) {
    if (e.lengthComputable) {
      $('progress').attr({ value: loadsize,max: total}); //更新数据到进度条
      var percent = loadsize / total * 100;
      $('#progress').html(percent.toFixed(2) + "%");
    }
  }
  
  
  
})(window, undefined)








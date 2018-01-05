
    // 初始化时间
    var now = new Date();
    var nowYear = now.getFullYear();
    var nowMonth = now.getMonth() + 1;
    var nowDate = now.getDate();
	var nowHour=now.getHours();
	var nowMin=now.getMinutes();
	
    showDateDom.attr('data-year', nowYear);
    showDateDom.attr('data-month', nowMonth);
    showDateDom.attr('data-date', nowDate);
	showDateDom.attr('data-hour',nowHour);
	showDateDom.attr('data-min',nowMin);

    // 数据初始化
	
	function formatHour(){
		var arr=[];
		for(var i=0;i<24;i++){
			arr.push({
				id:i+'',
				value:i+'时'
			});
		}
		
		return arr;
	}
	
	function formatMin(){
		var arr=[];
		for(var i=0;i<60;i++){
			arr.push({
				id:i+'',
				value:i+'分'
				
			});
		}
		
		return arr;
	}
	
    function formatYear (nowYear) {
        var arr = [];
        for (var i = nowYear - 5; i <= nowYear + 5; i++) {
            arr.push({
                id: i + '',
                value: i + '年'
            });
        }
        return arr;
    }
    function formatMonth () {
        var arr = [];
        for (var i = 1; i <= 12; i++) {
            arr.push({
                id: i + '',
                value: i + '月'
            });
        }
        return arr;
    }
    function formatDate (count) {
        var arr = [];
        for (var i = 1; i <= count; i++) {
            arr.push({
                id: i + '',
                value: i + '日'
            });
        }
        return arr;
    }
    var yearData = function(callback) {
        // settimeout只是模拟异步请求，真实情况可以去掉
        // setTimeout(function() {
            callback(formatYear(nowYear))
        // }, 2000)
    }
    var monthData = function (year, callback) {
        // settimeout只是模拟异步请求，真实情况可以去掉
        // setTimeout(function() {
            callback(formatMonth());
        // }, 2000);
    };
    var dateData = function (year, month, callback) {
        // settimeout只是模拟异步请求，真实情况可以去掉
        // setTimeout(function() {
            if (/^(1|3|5|7|8|10|12)$/.test(month)) {
                callback(formatDate(31));
            }
            else if (/^(4|6|9|11)$/.test(month)) {
                callback(formatDate(30));
            }
            else if (/^2$/.test(month)) {
                if (year % 4 === 0 && year % 100 !==0 || year % 400 === 0) {
                    callback(formatDate(29));
                }
                else {
                    callback(formatDate(28));
                }
            }
            else {
                throw new Error('month is illegal');
            }
        // }, 2000);
        // ajax请求可以这样写
        /*
        $.ajax({
            type: 'get',
            url: '/example',
            success: function(data) {
                callback(data);
            }
        });
        */
    };
    
	
	var dataHour=formatHour();
	
	var dataMin=formatMin();
	
	
	selectDateDom.bind('click', function () {
        var oneLevelId = showDateDom.attr('data-year');
        var twoLevelId = showDateDom.attr('data-month');
        var threeLevelId = showDateDom.attr('data-date');
		var fourLevelId = showDateDom.attr('data-hour');
		var fiveLevelId = showDateDom.attr('data-min');
        var iosSelect = new IosSelect(5, 
            [yearData, monthData, dateData,dataHour,dataMin],
            {
                title: '',
                itemHeight: 35,
                oneLevelId: oneLevelId,
                twoLevelId: twoLevelId,
                threeLevelId: threeLevelId,
				fourLevelId:fourLevelId,
				fiveLevelId:fiveLevelId,
                showLoading: true,
                callback: function (selectOneObj, selectTwoObj, selectThreeObj,four,five) {
                    showDateDom.attr('data-year', selectOneObj.id);
                    showDateDom.attr('data-month', selectTwoObj.id);
                    showDateDom.attr('data-date', selectThreeObj.id);
					showDateDom.attr('data-hour', four.id);
					showDateDom.attr('data-min', five.id);
                    showDateDom.val(selectOneObj.id + '-' + selectTwoObj.id + '-' + selectThreeObj.id+' '+ four.id+':'+five.id);
                }
        });
    });
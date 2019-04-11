//百度地图
var city;
var map = new BMap.Map("map");          // 创建地图实例  
var geoc = new BMap.Geocoder();               // 地址解析实例
var geolocation = new BMap.Geolocation();     // 浏览器解析实例
var point;
function showMap(){
    $("#map").show(); 
    load = layer.open({type: 2,content: '正在读取位置'});
    //获得位置信息
    var geoc = new BMap.Geocoder();               // 地址解析实例
    var geolocation = new BMap.Geolocation();     // 浏览器解析实例
    geolocation.getCurrentPosition(function(r){
        layer.close(load);
        if(this.getStatus() == BMAP_STATUS_SUCCESS){
            point = new BMap.Point(r.point.lng, r.point.lat);
            geoc.getLocation(point, function(rs){
                var addComp = rs.addressComponents; 
                city = addComp.city;
                map.centerAndZoom(r.point, 15);
                getShopList();
            });
        }else {
            alert('无法获取位置信息');
        }
    },{enableHighAccuracy: true});
}

//地图选点   
function getShopList(){ 
    $.ajax({
        url : '/index.php/Home/Buy/getAgent',
        dataType : 'json',
        type : 'post',
        data : {city:city},
        success : function(r){
            shopData = r;
            if (r.state==1) {
                _html = '';
                $.each(r.data,function(index,o){
                    var point = new BMap.Point(o.lng,o.lat);
                    addMarker(point,o.id,o.company);
                })      
            }
        }
    });
}

function addMarker(point , id , name){              
    var myLabel = new BMap.Label("", {offset:new BMap.Size(-10,-30), position:point});  //label的位置                  
        myLabel.setStyle({               //给label设置样式，任意的CSS都是可以的
        position: 'absolute',
        border: 'none',
        background: 'none',
        zIndex: '1',
        cursor:"pointer"
    });

    var con = '<div class="marker" id="'+id+'" title="'+name+'"></div>';
    myLabel.setContent(con);
    map.addOverlay(myLabel);                        //把label添加到地图上
            
    myLabel.addEventListener("click", function(){           
        layer.open({
            content:name,
            btn: ['设为默认'],
            skin: 'footer',
            yes: function(){
                layer.closeAll();
                $("#map").hide();
                $("#agentName").html(name);
                $("#shopID").val(id);
                $("#shopName").val(name);
            }
        });
    });
}
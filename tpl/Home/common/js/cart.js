utils = {
    setParam : function (name,value){
        localStorage.setItem(name,value)
    },
    getParam : function(name){
        return localStorage.getItem(name)
    }
}

product={
    id:0,
    name:"",
    num:0,
    price:0.00,
    optionID:0,
    parmName:"",
    intr:"",
};
orderdetail={
    username:"",
    phone:"",
    address:"",
    zipcode:"",
    totalNumber:0,
    totalAmount:0.00    
}
cart = {
    //向购物车中添加商品
    addproduct:function(product,cartName){
        var ShoppingCart = utils.getParam(cartName);
        if(ShoppingCart==null||ShoppingCart==""){
			//第一次加入商品
            var jsonstr = {"productlist":[{"id":product.id,"name":product.name,"num":product.num,"price":product.price,"optionID":product.optionID,"parmName":product.parmName,"intr":product.intr}],"totalNumber":product.num,"totalAmount":(product.price*product.num)};
            utils.setParam(cartName,"'"+JSON.stringify(jsonstr));
        }else{
            var jsonstr = JSON.parse(ShoppingCart.substr(1,ShoppingCart.length));
            var productlist = jsonstr.productlist;
            var result=false;
			//查找购物车中是否有该商品
            for(var i in productlist){
                if(productlist[i].id==product.id && productlist[i].optionID==product.optionID){
                    //productlist[i].num=parseInt(productlist[i].num)+parseInt(product.num);
                    result = true;
                }
            }
            if(!result){
				//没有该商品就直接加进去
                productlist.push({"id":product.id,"name":product.name,"num":product.num,"price":product.price,"optionID":product.optionID,"parmName":product.parmName,"intr":product.intr});
            }
			//重新计算总价
            jsonstr.totalNumber=parseInt(jsonstr.totalNumber)+parseInt(product.num);
            jsonstr.totalAmount=parseFloat(jsonstr.totalAmount)+(parseInt(product.num)*parseFloat(product.price));
            orderdetail.totalNumber = jsonstr.totalNumber;
            orderdetail.totalAmount = jsonstr.totalAmount;
            //保存购物车
            utils.setParam(cartName,"'"+JSON.stringify(jsonstr));
        }
    },
    //修改给买商品数量
    updateproductnum:function(id,num,cartName){
        var ShoppingCart = utils.getParam(cartName);
        var jsonstr = JSON.parse(ShoppingCart.substr(1,ShoppingCart.length));
        var productlist = jsonstr.productlist;
        
        for(var i in productlist){
            if(productlist[i].id==id){
                jsonstr.totalNumber=parseInt(jsonstr.totalNumber)+(parseInt(num)-parseInt(productlist[i].num));
                jsonstr.totalAmount=parseFloat(jsonstr.totalAmount)+((parseInt(num)*parseFloat(productlist[i].price))-parseInt(productlist[i].num)*parseFloat(productlist[i].price));
                productlist[i].num=parseInt(num);
                
                orderdetail.totalNumber = jsonstr.totalNumber;
                orderdetail.totalAmount = jsonstr.totalAmount;
                utils.setParam(cartName,"'"+JSON.stringify(jsonstr));
                return;
            }
        }
    },
    //获取购物车中的所有商品
    getproductlist:function(cartName){
        var ShoppingCart = utils.getParam(cartName);
        var jsonstr = JSON.parse(ShoppingCart.substr(1,ShoppingCart.length));
        var productlist = jsonstr.productlist;
        orderdetail.totalNumber = jsonstr.totalNumber;
        orderdetail.totalAmount = jsonstr.totalAmount;
        return productlist;
    },
    //判断购物车中是否存在商品
    existproduct:function(id,cartName){
        var ShoppingCart = utils.getParam(cartName);
        var jsonstr = JSON.parse(ShoppingCart.substr(1,ShoppingCart.length));
        var productlist = jsonstr.productlist;
        var result=false;
        for(var i in productlist){
            if(productlist[i].id==product.id){
                result = true;
            }
        }
        return result;
    },
    //删除购物车中商品
    deleteproduct:function(id,cartName){
        var ShoppingCart = utils.getParam(cartName);
        var jsonstr = JSON.parse(ShoppingCart.substr(1,ShoppingCart.length));
        var productlist = jsonstr.productlist;
        var list=[];
        for(var i in productlist){
            if(productlist[i].id==id){
                jsonstr.totalNumber=parseInt(jsonstr.totalNumber)-parseInt(productlist[i].num);
                jsonstr.totalAmount=parseFloat(jsonstr.totalAmount)-parseInt(productlist[i].num)*parseFloat(productlist[i].price);
            }else{
                list.push(productlist[i]);
            }
        }
        jsonstr.productlist = list;
        orderdetail.totalNumber = jsonstr.totalNumber;
        orderdetail.totalAmount = jsonstr.totalAmount;
        utils.setParam(cartName,"'"+JSON.stringify(jsonstr));
    }
};

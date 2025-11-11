package com.qujianma.app

import com.qujianma.app.Rule

/**
 * 内置规则管理器，用于管理ParcelInfoExtractor.kt中的内置正则规则
 */
object BuiltInRulesManager {
    
    /**
     * 获取内置的排除关键词规则
     */
    fun getSpecialExcludedKeywords(): List<String> {
        return listOf(
            "系统", "平台", "网站", "APP", "应用", "软件", "程序", "服务", "客服",
            "活动", "优惠", "促销", "折扣", "返现", "返利", "奖励", "积分", "会员",
            "注册", "登录", "密码", "账号", "账户", "充值", "支付", "付款", "退款",
            "订单", "购买", "购物", "商城", "商店", "店铺", "商品", "产品", "货物",
            "通知", "提醒", "公告", "通告", "消息", "信息",
            "生日", "礼包", "包邮", "拒收", "回复", "详情", "查看"
        )
    }
    
    /**
     * 获取内置的医疗相关关键词规则
     */
    fun getMedicalKeywords(): List<String> {
        return listOf(
            "医院", "门诊", "科室", "就诊", "挂号", "预约", "签到", "附院", "自助机"
        )
    }
    
    /**
     * 获取内置的电商相关关键词规则
     */
    fun getECommerceKeywords(): List<String> {
        return listOf(
            "美团", "饿了么", "淘宝", "天猫", "京东", "拼多多", "抖音", "快手", 
            "苏宁", "唯品会", "小红书", "网易严选", "小米有品", "华为商城", "Apple Store",
            "购买", "下单", "订单", "商品", "购物", "优选"
        )
    }
    
    /**
     * 获取内置的物流状态更新关键词规则
     */
    fun getLogisticsStatusKeywords(): List<String> {
        return listOf(
            "物流状态", "已更新", "问题", "解决", "处理详情", "尾号"
        )
    }
    
    /**
     * 获取内置的已完成状态关键词规则
     */
    fun getCompletedStatusKeywords(): List<String> {
        return listOf(
            "已完成", "已取件", "已签收", "已送达", "已领取", "已出库", "已发货",
            "完成取件", "取件完成"
        )
    }
    
    /**
     * 获取内置的退货/售后相关关键词规则
     */
    fun getReturnServiceKeywords(): List<String> {
        return listOf(
            "退货", "退款", "售后", "寄回", "二次销售", "退货快递", "退货单号", "退货申请", "维修",
            "服务单", "闪电退款", "退换"
        )
    }
    
    /**
     * 获取内置的银行相关关键词规则
     */
    fun getBankKeywords(): List<String> {
        return listOf(
            "银行", "信用卡", "储蓄卡", "账户", "余额", "转账", "汇款", "ATM", "网银", 
            "手机银行", "还款", "贷款", "利息", "理财", "基金", "股票", "保险", "证券",
            "面签", "实体卡片", "中国邮政", "邮政"
        )
    }
    
    /**
     * 获取内置的彩铃、视频等营销服务关键词规则
     */
    fun getRingServiceKeywords(): List<String> {
        return listOf(
            "视频彩铃", "彩铃", "彩铃包", "视频", "视讯", "来电", "新视界"
        )
    }
    
    /**
     * 获取内置的取件码正则表达式规则
     */
    fun getCodePattern(): String {
        return "(?<!\\d)([A-Z0-9]{1,2}[-]?){1,4}[A-Z0-9]{1,12}(?!\\d)"
    }
    
    /**
     * 获取内置的驿站名称正则表达式规则
     */
    fun getStationPattern(): String {
        return "(菜鸟驿站|妈妈驿站|快递驿站|代收点|圆通快递|申通快递|极兔速递|兔喜生活|袋鼠智柜|韵达超市|快递超市|菜鸟|顺丰|中通|EMS)"
    }
    
    /**
     * 获取内置的标签正则表达式规则
     */
    fun getTagPatterns(): List<String> {
        return listOf(
            "【([^】]+)】",  // 匹配第一个【】中的内容
            "(菜鸟驿站|妈妈驿站|快递驿站|代收点|圆通快递|申通快递|极兔速递|兔喜生活|袋鼠智柜|韵达超市|快递超市|菜鸟)"
        )
    }
    
    /**
     * 获取内置的地址正则表达式规则
     */
    fun getAddressPatterns(): List<String> {
        return listOf(
            // 通用地址提取模式
            "(地址[:：]|到)\\s*([^，。\\[\\]【】]+?(?=取件码|取货码|凭|请凭|,|，|。|$))",
            // 包含地标词的地址提取
            "((?:[^，。]*?(?:镇|街道|路|街|巷|村|区|市|县|乡|十字|政府|小区|门店|店|部|超市|驿站|塔)[^，。]*?)+?)\\s*(?=凭|取件码|取货码|,|，|$)",
            // 特定格式地址提取
            "([^，。]+(路|街|巷|村|镇|区|市|县|乡|十字|政府|小区|门店|店|部|超市|驿站)[^，。]+)"
        )
    }
    
    /**
     * 获取内置规则列表
     */
    fun getBuiltInRules(): List<Rule> {
        return mutableListOf<Rule>()
    }
}
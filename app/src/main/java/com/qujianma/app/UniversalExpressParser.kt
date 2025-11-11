package com.qujianma.app

import java.util.regex.Pattern
import java.util.regex.Matcher

/**
 * 通用快递短信解析器
 * 设计用于匹配各种快递短信格式的通用解析器
 */
object UniversalExpressParser {
    
    /**
     * 通用取件码正则表达式
     */
    private val CODE_PATTERNS = listOf(
        // 标准取件码格式
        Regex("取件码[:：]?\\s*([A-Z0-9\\-]+)"),
        Regex("取货码[:：]?\\s*([A-Z0-9\\-]+)"),
        Regex("验证码[:：]?\\s*([A-Z0-9\\-]+)"),
        Regex("凭\\s*([A-Z0-9\\-]+)\\s*(?:来|到|取)"),
        Regex("密码[:：]?\\s*([A-Z0-9\\-]+)"),
        Regex("代码[:：]?\\s*([A-Z0-9\\-]+)"),
        // 匹配独立的取件码（前后有明确边界）
        Regex("(?<!\\w)([A-Z0-9]{2,3}[-][A-Z0-9]{4,})(?!\\w)"),
        Regex("(?<!\\w)([A-Z0-9]{6,12})(?!\\w)")
    )
    
    /**
     * 通用驿站名称正则表达式
     */
    private val STATION_PATTERNS = listOf(
        Regex("【([^】]+)】"),
        Regex("\\[([^\\]]+)\\]"),
        Regex("(菜鸟驿站|妈妈驿站|快递驿站|代收点|圆通快递|申通快递|极兔速递|兔喜生活|袋鼠智柜|韵达超市|快递超市|菜鸟|顺丰|中通|EMS|京东)"),
        Regex("([^\\s]+(?:驿站|快递|代收点|服务站|自提点|营业部|超市|门店))")
    )
    
    /**
     * 通用地址正则表达式
     */
    private val ADDRESS_PATTERNS = listOf(
        Regex("(地址|到|位于|位置)[:：]?\\s*([^，。\\[\\]【】]+)"),
        Regex("([^，。]+(?:路|街|巷|村|镇|区|市|县|乡|十字|政府|小区|门店|店|部|超市|驿站|塔)[^，。]*)"),
        Regex("((?:[^，。]*?(?:镇|街道|路|街|巷|村|区|市|县|乡|十字|政府|小区|门店|店|部|超市|驿站|塔)[^，。]*?)+?)\\s*(?=凭|取件码|取货码|,|，|$)"),
        Regex("([^，。]*?[路街巷村镇区市县乡十字政府小区门店店部超市驿站塔][^，。]*)")
    )
    
    /**
     * 解析短信内容，提取快递信息
     * @param smsContent 短信内容
     * @return 解析结果Map，包含code、station、address等字段
     */
    fun parse(smsContent: String): Map<String, Any?> {
        val result = mutableMapOf<String, Any?>()
        
        // 提取取件码
        val code = extractCode(smsContent)
        if (code != null) {
            result["code"] = code
        }
        
        // 提取驿站名称
        val station = extractStation(smsContent)
        if (station != null) {
            result["station"] = station
        }
        
        // 提取地址
        val address = extractAddress(smsContent)
        if (address != null) {
            result["address"] = address
        }
        
        return result
    }
    
    /**
     * 提取取件码
     */
    private fun extractCode(smsContent: String): String? {
        for (pattern in CODE_PATTERNS) {
            val matcher = pattern.find(smsContent)
            if (matcher != null) {
                val code = matcher.groupValues[1].trim()
                // 验证取件码有效性
                if (isValidPickupCode(code)) {
                    return code
                }
            }
        }
        return null
    }
    
    /**
     * 提取驿站名称
     */
    private fun extractStation(smsContent: String): String? {
        for (pattern in STATION_PATTERNS) {
            val matcher = pattern.find(smsContent)
            if (matcher != null) {
                // 对于匹配组的情况
                if (matcher.groupValues.size > 1) {
                    return matcher.groupValues[1].trim()
                } else {
                    // 对于直接匹配的情况
                    return matcher.value.trim()
                }
            }
        }
        return null
    }
    
    /**
     * 提取地址
     */
    private fun extractAddress(smsContent: String): String? {
        for (pattern in ADDRESS_PATTERNS) {
            val matcher = pattern.find(smsContent)
            if (matcher != null) {
                // 根据不同模式获取地址
                val address = if (matcher.groupValues.size > 2) {
                    // 有分组的情况，取第二个分组（第一个是完整匹配，第二个是第一个分组）
                    matcher.groupValues[2].trim()
                } else if (matcher.groupValues.size > 1) {
                    // 只有一个分组的情况
                    matcher.groupValues[1].trim()
                } else {
                    // 没有分组的情况
                    matcher.value.trim()
                }
                
                if (address.isNotEmpty() && address.length > 1) {
                    return cleanAddress(address)
                }
            }
        }
        return null
    }
    
    /**
     * 验证取件码是否有效
     */
    private fun isValidPickupCode(code: String): Boolean {
        // 检查是否为空
        if (code.isEmpty()) {
            return false
        }
        
        // 检查是否只包含数字、字母和连字符
        if (!code.matches(Regex("^[A-Za-z0-9\\-]+$"))) {
            return false
        }
        
        // 检查是否包含至少一个数字
        if (!code.any { it.isDigit() }) {
            return false
        }
        
        // 长度应在合理范围内
        if (code.length < 2 || code.length > 20) {
            return false
        }
        
        // 排除明显不是取件码的格式
        val invalidPatterns = listOf(
            Regex("\\d{4}-\\d{2}-\\d{2}"), // 日期格式
            Regex("\\d{1,2}:\\d{2}"), // 时间格式
            Regex("\\d{11}"), // 手机号格式
            Regex("\\d{6}") // 邮政编码格式
        )
        
        for (pattern in invalidPatterns) {
            if (code.matches(pattern)) {
                return false
            }
        }
        
        return true
    }
    
    /**
     * 清理地址字符串
     */
    private fun cleanAddress(address: String): String {
        var cleaned = address.trim()
        
        // 移除末尾的提示性文字
        val promptEndings = listOf(
            "请", "请您", "佩戴", "口罩", "个人", "防护",
            "及时", "取件", "联系", "电话", "取包裹", "领取包裹"
        )
        
        for (ending in promptEndings) {
            if (cleaned.endsWith(ending)) {
                cleaned = cleaned.substring(0, cleaned.length - ending.length).trim()
            }
        }
        
        // 移除逗号后的内容
        val commaIndex = cleaned.indexOf(',')
        if (commaIndex > 0) {
            cleaned = cleaned.substring(0, commaIndex)
        }
        
        val fullCommaIndex = cleaned.indexOf('，')
        if (fullCommaIndex > 0) {
            cleaned = cleaned.substring(0, fullCommaIndex)
        }
        
        return cleaned.ifEmpty { "地址未知" }
    }
}
var jq = $

/**
 * 操作成功标示码
 * @type {number}
 */
var SUCCESS_CODE = 1

/**
 * 操作失败标示码
 * @type {number}
 */
var FAIL_CODE = 0

/**
 * 操作未授权标示码
 * @type {number}
 */
var UNAUTHORIZED_CODE = 401

/**
 * 请求地址
 * @type {string}
 */
var REQUEST_ADDRESS = 'http://127.0.0.1:9501'

/**
 * 缓存key
 * @type {string}
 */
var ADMIN_ACCESS_TOKEN_KEY = 'ADMIN_AUTH_ACCESS_TOKEN'

/**
 * 获取接口请求地址
 * @param url
 * @returns {string}
 */
var getRequestUrl = function (url) {
    return REQUEST_ADDRESS + url
}

/**
 * 设置缓存access_token
 * @param value
 */
var setAdminAccessTokenCache = function (value) {
    sessionStorage.setItem(ADMIN_ACCESS_TOKEN_KEY, value)
}

/**
 * 删除缓存access_token
 */
var deleteAdminAccessTokenCache = function () {
    sessionStorage.removeItem(ADMIN_ACCESS_TOKEN_KEY)
}


/**
 * 获取access_token
 * @returns {string}
 */
var getAdminAccessTokenCache = function () {
    return sessionStorage.getItem(ADMIN_ACCESS_TOKEN_KEY)
}

/**
 * 判断是否有缓存access_token
 * @returns {boolean}
 */
var hasLocalAdminAccessTokenCache = function () {
    return sessionStorage.getItem(ADMIN_ACCESS_TOKEN_KEY) !== null
}

/**
 * 操作警告弹窗
 * @param message
 * @param callback
 */
var layerWarning = function (message, callback = null) {
    layer.msg(message, {icon: 7, time: 2000}, callback)
}

/**
 * 操作失败弹窗
 * @param message
 * @param callback
 */
var layerFail = function (message, callback = null) {
    layer.msg(message, {icon: 5, time: 2000}, callback)
}

/**
 * 操作成功弹窗
 * @param message
 * @param callback
 */
var layerSuccess = function (message, callback = null) {
    layer.msg(message, {icon: 1, time: 1000}, callback)
}

/**
 * loader值
 */
var openLoadIndex

/**
 * 打开加载提示
 * @param type
 */
var openLoad = function (type = 1) {
    openLoadIndex = layer.load(type)
}

/**
 * 关闭加载提示
 * @param layerIndex
 */
var closeLoad = function (layerIndex = openLoadIndex) {
    layer.close(layerIndex)
}

/**
 * 统一ajaxPost请求
 * post方式
 * json数据格式
 * @param url
 * @param data
 * @param callback
 */
var ajaxPost = function (url, data, callback) {
    jq.ajax({
        url: url,
        data: data,
        type: 'post',
        dataType: 'json',
        headers: {'Authentication': getAdminAccessTokenCache()},
        success: callback,
    })
}

/**
 * 请求不成功处理函数
 * @param response
 * @param callback
 */
var handleUnSuccess = function (response, callback = null) {
    if (isResponseUnauthorized(getResponseCode(response))) {
        layerWarning(getResponseMessage(response), function () {
            redirectToLogin()
        })
    }
    if (isResponseFail(getResponseCode(response))) {
        layerFail(getResponseMessage(response), callback)
    }
}

/**
 * 判断是否是失败
 * @param code
 * @returns {boolean}
 */
var isResponseFail = function (code) {
    return code === FAIL_CODE
}

/**
 * 判断是否是成功
 * @param code
 * @returns {boolean}
 */
var isResponseSuccess = function (code) {
    return code === SUCCESS_CODE
}

/**
 * 判断请求是否未经授权
 * @param code
 * @returns {boolean}
 */
var isResponseUnauthorized = function (code) {
    return code === UNAUTHORIZED_CODE
}

/**
 * 获取返回数据的code
 * @param response
 * @returns {*}
 */
var getResponseCode = function (response) {
    return response.code
}

/**
 * 获取返回数据的message
 * @param response
 * @returns {*}
 */
var getResponseMessage = function (response) {
    return response.message
}

/**
 * 获取返回数据的data
 * @param response
 * @returns {*}
 */
var getResponseData = function (response) {
    return response.data
}

/**
 * 获取返回数据的type[action|request]
 * @param response
 * @returns {*}
 */
var getResponseType = function (response) {
    return response.type
}

/**
 * 跳转到登录页面
 */
var redirectToLogin = function () {
    jq(location).attr('href', 'login.html')
}

/**
 * 跳转到首页
 */
var redirectToIndex = function () {
    jq(location).attr('href', 'index.html')
}

/**
 * 添加layui模块扩展
 */
layui.extend({
    dtree: '/static/plugins/layui_ext/dtree/dtree',
})



package com.qujianma.app

import android.content.Context
import androidx.recyclerview.widget.ItemTouchHelper
import androidx.recyclerview.widget.RecyclerView

class ExpressSwipeCallback(
    private val context: Context,
    private val onItemSwiped: (position: Int, action: SwipeAction) -> Unit
) : SwipeActionCallback() {
    
    enum class SwipeAction {
        DELETE,
        NOTE,
        MARK
    }
    
    override fun onSwipeLeft(position: Int) {
        // 这里需要根据滑动的位置确定用户点击的是哪个按钮
        // 简化实现，总是触发第一个操作（删除）
        onItemSwiped(position, SwipeAction.DELETE)
    }
}
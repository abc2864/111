package com.qujianma.app

import android.graphics.Canvas
import android.graphics.Paint
import android.graphics.RectF
import android.graphics.drawable.Drawable
import androidx.recyclerview.widget.ItemTouchHelper
import androidx.recyclerview.widget.RecyclerView
import androidx.core.content.ContextCompat
import android.graphics.drawable.ColorDrawable

abstract class SwipeActionCallback : ItemTouchHelper.SimpleCallback(0, ItemTouchHelper.LEFT) {
    
    private val deleteColor = 0xFFE94057.toInt()
    private val noteColor = 0xFF2196F3.toInt()
    private val markColor = 0xFF4CAF50.toInt()
    
    private val deletePaint = Paint().apply { color = deleteColor }
    private val notePaint = Paint().apply { color = noteColor }
    private val markPaint = Paint().apply { color = markColor }
    
    private var deleteBounds = RectF()
    private var noteBounds = RectF()
    private var markBounds = RectF()
    
    override fun onMove(
        recyclerView: RecyclerView,
        viewHolder: RecyclerView.ViewHolder,
        target: RecyclerView.ViewHolder
    ): Boolean {
        return false
    }
    
    override fun onChildDraw(
        c: Canvas,
        recyclerView: RecyclerView,
        viewHolder: RecyclerView.ViewHolder,
        dX: Float,
        dY: Float,
        actionState: Int,
        isCurrentlyActive: Boolean
    ) {
        if (actionState == ItemTouchHelper.ACTION_STATE_SWIPE) {
            val itemView = viewHolder.itemView
            
            // Draw backgrounds
            if (dX < 0) { // Swiping left
                // Calculate button widths
                val buttonWidth = itemView.width / 3
                val deleteLeft = itemView.right + dX
                val noteLeft = deleteLeft + buttonWidth
                val markLeft = noteLeft + buttonWidth
                
                // Draw delete background
                deleteBounds = RectF(
                    deleteLeft.toFloat(),
                    itemView.top.toFloat(),
                    deleteLeft + buttonWidth.toFloat(),
                    itemView.bottom.toFloat()
                )
                c.drawRect(deleteBounds, deletePaint)
                
                // Draw note background
                noteBounds = RectF(
                    noteLeft.toFloat(),
                    itemView.top.toFloat(),
                    noteLeft + buttonWidth.toFloat(),
                    itemView.bottom.toFloat()
                )
                c.drawRect(noteBounds, notePaint)
                
                // Draw mark background
                markBounds = RectF(
                    markLeft.toFloat(),
                    itemView.top.toFloat(),
                    markLeft + buttonWidth.toFloat(),
                    itemView.bottom.toFloat()
                )
                c.drawRect(markBounds, markPaint)
                
                // Draw icons
                drawIcons(c, recyclerView, deleteBounds, noteBounds, markBounds)
            }
            
            super.onChildDraw(c, recyclerView, viewHolder, dX, dY, actionState, isCurrentlyActive)
        }
    }
    
    private fun drawIcons(c: Canvas, recyclerView: RecyclerView, deleteBounds: RectF, noteBounds: RectF, markBounds: RectF) {
        // Draw delete icon
        val deleteIcon = ContextCompat.getDrawable(recyclerView.context, android.R.drawable.ic_menu_delete)
        deleteIcon?.let {
            drawIcon(c, it, deleteBounds)
        }
        
        // Draw note icon
        val noteIcon = ContextCompat.getDrawable(recyclerView.context, android.R.drawable.ic_menu_edit)
        noteIcon?.let {
            drawIcon(c, it, noteBounds)
        }
        
        // Draw mark icon
        val markIcon = ContextCompat.getDrawable(recyclerView.context, android.R.drawable.ic_menu_view)
        markIcon?.let {
            drawIcon(c, it, markBounds)
        }
    }
    
    private fun drawIcon(c: Canvas, icon: Drawable, bounds: RectF) {
        val iconMargin = (bounds.height() - icon.intrinsicHeight) / 2
        val iconTop = bounds.top + iconMargin
        val iconBottom = iconTop + icon.intrinsicHeight
        val iconLeft = bounds.left + (bounds.width() - icon.intrinsicWidth) / 2
        val iconRight = iconLeft + icon.intrinsicWidth
        
        icon.setBounds(iconLeft.toInt(), iconTop.toInt(), iconRight.toInt(), iconBottom.toInt())
        icon.draw(c)
    }
    
    override fun onSwiped(viewHolder: RecyclerView.ViewHolder, direction: Int) {
        val position = viewHolder.adapterPosition
        when (direction) {
            ItemTouchHelper.LEFT -> {
                // Handle left swipe
                onSwipeLeft(position)
            }
        }
    }
    
    abstract fun onSwipeLeft(position: Int)
}
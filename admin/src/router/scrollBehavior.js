import { useViewTagsStore } from '@/stores/useViewTagsStore'
import { nextTick } from 'vue'

export function beforeEach(to, from){
  const adminMain = document.querySelector('#adminui-main');
  if(!adminMain){return false}
  const viewTagsStore = useViewTagsStore()

  viewTagsStore.updateViewTags({
    fullPath: from.fullPath,
    scrollTop: adminMain.scrollTop
  })
}

export function afterEach(to){
  const adminMain = document.querySelector('#adminui-main');
  if(!adminMain){return false}
  nextTick(() => {
    const viewTagsStore = useViewTagsStore()
    const beforeRoute = viewTagsStore.viewTags.filter(v => v.fullPath === to.fullPath)[0];
    if (beforeRoute) {
      adminMain.scrollTop = beforeRoute.scrollTop || 0
    }
  }).then(r => {})
}

<template>
  <div>
    <div class="page-header">
      <div><h2>托管签到</h2><p>托管班每日签到签退管理</p></div>
      <div class="header-actions">
        <el-date-picker v-model="dateFilter" type="date" value-format="YYYY-MM-DD" @change="loadData" style="width:160px;margin-right:8px" />
        <el-button type="primary" @click="showQuickCheckin=true">快速签到</el-button>
      </div>
    </div>
    <el-card shadow="never">
      <el-table :data="list" stripe v-loading="loading" border>
        <el-table-column label="学员" width="120"><template #default="{row}">{{row.student_name||'-'}}</template></el-table-column>
        <el-table-column prop="checkin_date" label="日期" width="120" />
        <el-table-column prop="checkin_time" label="签到" width="80" align="center">
          <template #default="{row}">{{row.checkin_time||'-'}}</template>
        </el-table-column>
        <el-table-column prop="checkout_time" label="签退" width="80" align="center">
          <template #default="{row}">{{row.checkout_time||'-'}}</template>
        </el-table-column>
        <el-table-column label="状态" width="70" align="center">
          <template #default="{row}"><el-tag :type="row.status==2?'success':'primary'" size="small">{{row.status==2?'已签退':'已签到'}}</el-tag></template>
        </el-table-column>
        <el-table-column prop="pickup_person" label="接的人" width="100" />
        <el-table-column prop="remark" label="备注" min-width="150" />
        <el-table-column label="操作" width="100" fixed="right">
          <template #default="{row}">
            <el-button v-if="row.status==1" text type="success" size="small" @click="doCheckout(row)">签退</el-button>
          </template>
        </el-table-column>
      </el-table>
      <div class="table-footer"><el-pagination v-model:current-page="page" :page-size="pageSize" :total="total" layout="total,prev,pager,next" background small /></div>
    </el-card>
    <el-dialog v-model="showQuickCheckin" title="快速签到" width="400px">
      <el-form :model="checkinForm" label-width="80px">
        <el-form-item label="学员" required><el-select v-model="checkinForm.student_id" filterable style="width:100%"><el-option v-for="s in students" :key="s.id" :label="s.username" :value="s.id" /></el-select></el-form-item>
        <el-form-item label="日期"><el-date-picker v-model="checkinForm.checkin_date" type="date" value-format="YYYY-MM-DD" style="width:100%" /></el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="showQuickCheckin=false">取消</el-button>
        <el-button type="primary" @click="doCheckin" :loading="checkinLoading">签到</el-button>
      </template>
    </el-dialog>
  </div>
</template>
<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import http from '../../utils/http'
const BASE='http://47.114.125.123'
const list=ref([]),page=ref(1),pageSize=15,total=ref(0),loading=ref(false)
const dateFilter=ref(''),showQuickCheckin=ref(false),checkinLoading=ref(false)
const students=ref([])
const checkinForm=reactive({student_id:0,checkin_date:''})
async function loadData(){
  loading.value=true
  try{
    const params={page:page.value,pageSize}
    if(dateFilter.value)params.checkin_date=dateFilter.value
    const res=await http.get(BASE+'/m/Admin/c/Api/a/aftercareCheckinList',{params})
    list.value=res.data.list||[];total.value=res.data.total||0
  }catch(e){console.error(e)}finally{loading.value=false}
}
async function loadStudents(){
  const res=await http.get(BASE+'/m/Admin/c/Api/a/students')
  students.value=res.data.list||[]
}
async function doCheckin(){
  if(!checkinForm.student_id){ElMessage.warning('请选择学员');return}
  checkinLoading.value=true
  try{
    const p=new URLSearchParams()
    p.append('student_id',checkinForm.student_id)
    if(checkinForm.checkin_date)p.append('checkin_date',checkinForm.checkin_date)
    await http.post(BASE+'/m/Admin/c/Api/a/aftercareCheckin',p.toString(),{headers:{'Content-Type':'application/x-www-form-urlencoded'}})
    ElMessage.success('签到成功');showQuickCheckin.value=false;loadData()
  }catch(e){ElMessage.error(e.response?.data?.msg||'签到失败')}finally{checkinLoading.value=false}
}
async function doCheckout(row){
  try{
    const p=new URLSearchParams();p.append('student_id',row.student_id);p.append('checkin_date',row.checkin_date)
    await http.post(BASE+'/m/Admin/c/Api/a/aftercareCheckout',p.toString(),{headers:{'Content-Type':'application/x-www-form-urlencoded'}})
    ElMessage.success('签退成功');loadData()
  }catch(e){ElMessage.error(e.response?.data?.msg||'签退失败')}
}
onMounted(()=>{loadData();loadStudents();if(!dateFilter.value){const d=new Date();dateFilter.value=d.getFullYear()+'-'+String(d.getMonth()+1).padStart(2,'0')+'-'+String(d.getDate()).padStart(2,'0')}})
</script>
<style scoped>
.page-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px}
.header-actions{display:flex;align-items:center}
.page-header h2{font-size:22px;font-weight:600;color:#1a1a2e;margin-bottom:4px}
.page-header p{font-size:13px;color:#909399}
.table-footer{display:flex;justify-content:flex-end;padding:16px 0 0}
</style>

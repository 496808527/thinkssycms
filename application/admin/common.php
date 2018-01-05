<?php

if (!function_exists("drawPermTree")) {

  function drawPermTree($nodes, $noborder = false) {
    if (!empty($nodes)) {
      $tree = "";
      if ($noborder) {
        $temp = "border_none";
      } else {
        $temp = "";
      }
      foreach ($nodes as $key => $value) {
        $title = $value->status ? '禁用' : '启用';
        $icon = $value->status ? '&#xe6dd;' : '&#xe6e1;';
        $tempstr = $value["permcss"] ? $value["permcss"] : '&nbsp;';
        $tree .= '<tr  class="text-l childs-' . $value["parentid"] . '" style="display:none;"><td class="' . $temp . '"></td><td colspan="6" class=" ' . $temp . '">';
        $tree .= '<table>';
        $tree .= '<tr class="text-c" data-id="' . $value["id"] . '" data-show="no"><td class="border_none " >|</td>';
        $tree .= '<td class="border_none col-md-1">' . $value["permname"] . '</td>';
        $tree .= '<td class="border_none col-md-3">' . $value["permurl"] . '</td>';
        $tree .= '<td class="border_none col-md-2">' . $tempstr . '</td>';
        $tree .= '<td class="border_none col-md-3">' . $value["remark"] . '</td>';
        $str = $value["status"] ? "<font color='green'>有效</font>" : "<font color='red'>无效</font>";
        $tree .= '<td class="border_none col-md-1">' . $str . '</td>';
        $tree .= '<td class="f-14 border_none col-md-2">
              <a title="编辑" href="javascript:;" onclick="admin_role_edit(\'权限编辑\', \'' . url("role/manageperm", ["id" => $value["id"]]) . '\',' . $value["id"] . ')" style="text-decoration:none">
                <i class="Hui-iconfont">&#xe6df;</i>
              </a> 
              <a title="' . $title . '" href="javascript:;" onclick="admin_role_del(this, ' . $value["id"] . ')" class="ml-5" style="text-decoration:none">
                <i class="Hui-iconfont">' . $icon . '</i>
              </a>
            </td></tr>';
        $tree .= drawPermTree($value["childs"], TRUE);
        $tree .= '</table>';
        $tree .= "</td></tr>";
      }
      return $tree;
    } else {
      return "";
    }
  }

}

if (!function_exists("role_permlist")) {
  function role_permlist($nodes, $checknodes, $nodesname) {
    if (empty($nodes)) {
      return "";
    } else {
      $strnodes = "";
      foreach ($nodes as $key => $node) {
        $checked = in_array($node["id"], $checknodes)?'checked="checked"':"";
        $strnodes .= '<dl class="cl permission-list2">';
        $strnodes .= '<dt><label class=""><input type="checkbox" '.$checked.' value="' . $node["id"] . '" name="' . $nodesname . '" id="user-Character-0-0">' . $node["permname"] . '
                    </label>';
        $strnodes .= "</dt>";
        $strnodes .= permchilds($node["childs"],$checknodes,$nodesname);
        $strnodes .= '</dl>';
      }
      return $strnodes;
    }
  }
  
  function permchilds($nodes,$checknodes,$nodesname){
    $childstr="<dd>";
    foreach ($nodes as $key=>$node){
      $checked = in_array($node["id"], $checknodes)?'checked="checked"':"";
      $childstr .= '<label class=""><input type="checkbox" '.$checked.' value="' . $node["id"] . '" name="' . $nodesname . '" id="user-Character-0-0">' . $node["permname"] . '
                    </label>';
    }
    $childstr.='</dd>';
    return $childstr;
  }
}








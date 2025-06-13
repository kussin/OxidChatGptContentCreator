[{include file="headitem.tpl" title="Category Weight Ranges"}]


<form name="myedit" id="myedit" action="[{$oViewConf->getSelfLink()}]" method="post">
    [{$oViewConf->getHiddenSid()}]
    <input type="hidden" name="cl" value="chatgpt_weight_range">
    <input type="hidden" name="fnc" value="save">
    <input type="hidden" name="editlanguage" value="[{$editlanguage}]">

    <table>
        <thead>
        <tr>
            <th>[{oxmultilang ident="KUSSIN_CHATGPT_CATEGORY_PATH"}]</th>
            <th>[{oxmultilang ident="KUSSIN_CHATGPT_CATEGORY_MIN_WEIGHT"}]</th>
            <th>[{oxmultilang ident="KUSSIN_CHATGPT_CATEGORY_MAX_WEIGHT"}]</th>
            <th>[{oxmultilang ident="KUSSIN_CHATGPT_CATEGORY_DEFAULT_WEIGHT"}]</th>
        </tr>
        </thead>
        <tbody>
        [{foreach from=$aCategoryPaths item=catPath}]
            [{assign var="range" value=$aRanges[$catPath]}]
            <tr>
                <td>[{$catPath}]</td>
                <td><input type="text" name="ranges[[{$catPath|escape:'html'}]][min]" value="[{$range.min|default:''}]"></td>
                <td><input type="text" name="ranges[[{$catPath|escape:'html'}]][max]" value="[{$range.max|default:''}]"></td>
                <td><input type="text" name="ranges[[{$catPath|escape:'html'}]][default]" value="[{$range.default|default:''}]"></td>
            </tr>
            [{/foreach}]
        </tbody>
    </table>
    <button class="btn" type="submit" name="fnc" value="save">Save</button>
</form>


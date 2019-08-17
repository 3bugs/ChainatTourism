package th.ac.dusit.dbizcom.chainattourism.model;

import com.google.gson.annotations.SerializedName;

import java.util.List;

public class District {

    public final int id;
    public final String name;
    public final String[] subDistrictNameList;
    public final int imageRes;

    public District(int id, String name, String[] subDistrictNameList, int imageRes) {
        this.id = id;
        this.name = name;
        this.subDistrictNameList = subDistrictNameList;
        this.imageRes = imageRes;
    }
}

package th.ac.dusit.dbizcom.chainattourism.net;

import retrofit2.Call;
import retrofit2.http.GET;

public interface WebServices {

    @GET("get_place")
    Call<GetPlaceResponse> getPlace(
    );

}